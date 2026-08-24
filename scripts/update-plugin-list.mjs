import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';
import { fileURLToPath } from 'node:url';

const repositoryRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const pluginsDirectory = path.join(repositoryRoot, 'mu-plugins');
const readmePath = path.join(repositoryRoot, 'README.MD');
const checkOnly = process.argv.includes('--check');

const ignoredFiles = new Set(['index.php', '000-autoloader.php']);
const fields = {
  name: /Plugin Name:\s*(.+)$/imu,
  description: /Description:\s*(.+)$/imu,
  version: /Version:\s*(.+)$/imu,
  requiresWP: /Requires at least:\s*(.+)$/imu,
  requiresPHP: /Requires PHP:\s*(.+)$/imu,
};

function firstDocblock(content) {
  return content.match(/^\s*<\?php\s*\/\*\*([\s\S]*?)\*\//u)?.[1] ?? '';
}

function extractPlugin(filePath) {
  const header = firstDocblock(fs.readFileSync(filePath, 'utf8'))
    .split(/\r?\n/u)
    .map((line) => line.replace(/^\s*\*\s?/u, ''))
    .join('\n');
  const plugin = { file: path.relative(repositoryRoot, filePath).split(path.sep).join('/') };

  for (const [field, pattern] of Object.entries(fields)) {
    plugin[field] = header.match(pattern)?.[1].trim() ?? '';
  }

  return plugin.name ? plugin : null;
}

function scanDirectory(directory, plugins) {
  const entries = fs.readdirSync(directory, { withFileTypes: true })
    .sort((a, b) => a.name.localeCompare(b.name, 'en'));

  for (const entry of entries) {
    const fullPath = path.join(directory, entry.name);

    if (entry.isDirectory()) {
      scanDirectory(fullPath, plugins);
    } else if (entry.isFile() && entry.name.endsWith('.php') && !ignoredFiles.has(entry.name)) {
      const plugin = extractPlugin(fullPath);
      if (plugin) plugins.push(plugin);
    }
  }
}

function scanPlugins() {
  if (!fs.existsSync(pluginsDirectory)) {
    throw new Error(`MU-plugins directory not found: ${pluginsDirectory}`);
  }

  const plugins = [];
  scanDirectory(pluginsDirectory, plugins);
  return plugins.sort((a, b) => a.name.localeCompare(b.name, 'en'));
}

function escapeCell(value) {
  return value.replace(/\s+/gu, ' ').replaceAll('|', '\\|').trim();
}

function minimumVersion(value) {
  return value ? `${escapeCell(value)}+` : 'N/A';
}

function generateTable(plugins) {
  if (plugins.length === 0) return '> No MU plugins found.';

  const rows = [
    '| Plugin Name | Description | Version | WP | PHP |',
    '|---|---|---:|---:|---:|',
  ];

  for (const plugin of plugins) {
    rows.push(
      `| [${escapeCell(plugin.name)}](${plugin.file}) | ${escapeCell(plugin.description) || 'N/A'} | ${escapeCell(plugin.version) || 'N/A'} | ${minimumVersion(plugin.requiresWP)} | ${minimumVersion(plugin.requiresPHP)} |`,
    );
  }

  return rows.join('\n');
}

function updatedReadme(table) {
  const startMarker = '<!-- PLUGIN_LIST_START -->';
  const endMarker = '<!-- PLUGIN_LIST_END -->';
  const content = fs.readFileSync(readmePath, 'utf8');
  const start = content.indexOf(startMarker);
  const end = content.indexOf(endMarker);

  if (start === -1 || end === -1 || end < start) {
    throw new Error('README plugin-list markers are missing or out of order.');
  }

  return `${content.slice(0, start + startMarker.length)}\n${table}\n${content.slice(end)}`;
}

const plugins = scanPlugins();
const currentReadme = fs.readFileSync(readmePath, 'utf8');
const nextReadme = updatedReadme(generateTable(plugins));

console.log(`Found ${plugins.length} MU plugins.`);

if (nextReadme === currentReadme) {
  console.log('README plugin list is up to date.');
} else if (checkOnly) {
  console.error('README plugin list is out of date. Run: node scripts/update-plugin-list.mjs');
  process.exitCode = 1;
} else {
  fs.writeFileSync(readmePath, nextReadme);
  console.log('README plugin list updated.');
}
