# Statamic Content Usage

> Track and analyze asset and entry usage across your Statamic site. Identify which assets and entries are being used, find unused content, and export detailed reports to CSV.

## Features

- **Asset Usage Tracking**: Discover which assets are used across content sources
- **Entry Usage Tracking**: Track which entries from a specific collection are referenced across content sources
- **Unused Content Detection**: Identify assets and entries that aren't being used anywhere
- **CSV Exports**: Export usage reports and unused content lists to CSV files
- **Dashboard Widgets**: Quick access to exports directly from the Statamic control panel
- **Artisan Commands**: Export reports via command line for automation and scheduled tasks
- **Container Filtering**: Filter unused assets by specific asset containers
- **Collection Tracking**: Track entry usage for any collection (e.g., global blocks, blog posts)

## Installation

You can install this addon via Composer:

```bash
composer require justbetter/statamic-content-usage
```

## Usage

### Dashboard Widgets

After installation, you'll find two widgets available in your Statamic dashboard:

#### Asset Usage Widget

The Asset Usage widget allows you to:
- Export a report of all assets and where they're used
- Export a list of unused assets (optionally filtered by container)

#### Entry Usage Widget

The Entry Usage widget allows you to:
- Select a collection to track
- Export a report of entries from that collection and where they're referenced
- Export a list of unused entries from that collection

### Artisan Commands

#### Export Asset Usage

Export a CSV report showing which assets are used across content sources:

```bash
php artisan content-usage:export-assets
```

**Options:**
- `--output=path/to/file.csv` - Specify a custom output path (default: `storage/app/asset_usage_YYYY-MM-DD_HHMMSS.csv`)

**Example:**
```bash
php artisan content-usage:export-assets --output=/tmp/asset-report.csv
```

#### Export Unused Assets

Export a CSV list of assets that aren't being used anywhere:

```bash
php artisan content-usage:export-unused-assets
```

**Options:**
- `--containers=container1,container2` - Filter by specific asset container handles (comma-separated)
- `--output=path/to/file.csv` - Specify a custom output path (default: `storage/app/unused_assets_YYYY-MM-DD_HHMMSS.csv`)

**Examples:**
```bash
# Export all unused assets
php artisan content-usage:export-unused-assets

# Export unused assets from specific containers
php artisan content-usage:export-unused-assets --containers=main,images

# Custom output path
php artisan content-usage:export-unused-assets --output=/tmp/unused.csv
```

#### Export Entry Usage

Export a CSV report showing which entries from a collection are referenced across content sources:

```bash
php artisan content-usage:export-entries --collection=blog
```

**Options:**
- `--collection=handle` - **Required.** The handle of the collection to track
- `--output=path/to/file.csv` - Specify a custom output path (default: `storage/app/entry_usage_{collection}_YYYY-MM-DD_HHMMSS.csv`)

**Example:**
```bash
php artisan content-usage:export-entries --collection=global --output=/tmp/global-usage.csv
```

#### Export Unused Entries

Export a CSV list of entries from a collection that aren't referenced anywhere:

```bash
php artisan content-usage:export-unused-entries blog
```

**Options:**
- `collection` - **Required.** The handle of the collection (as an argument)
- `--output=path/to/file.csv` - Specify a custom output path (default: `storage/app/unused_entries_{collection}_YYYY-MM-DD_HHMMSS.csv`)

**Example:**
```bash
php artisan content-usage:export-unused-entries global --output=/tmp/unused-global.csv
```

## CSV Export Formats

### Asset Usage Export

| Column | Description |
|--------|-------------|
| Asset Path | The path to the asset file |
| Asset URL | The public URL of the asset |
| Asset Basename | The filename of the asset |
| Source Title | The title of the content source using the asset |
| Source URL | The URL of the content source (when available) |
| Source Type | The source type/handle (e.g. collection, globals, navigation, taxonomy) |

### Entry Usage Export

| Column | Description |
|--------|-------------|
| Entry Title | The title of the referenced entry |
| Entry URL | The URL of the referenced entry |
| Entry Collection | The collection handle of the referenced entry |
| Source Title | The title of the content source referencing it |
| Source URL | The URL of the content source (when available) |
| Source Type | The source type/handle (e.g. collection, globals, navigation, taxonomy) |

### Unused Assets Export

| Column | Description |
|--------|-------------|
| Asset Path | The path to the asset file |
| Asset URL | The public URL of the asset |
| Asset Basename | The filename of the asset |
| Container | The asset container handle |

### Unused Entries Export

| Column | Description |
|--------|-------------|
| Entry ID | The ID of the unused entry |
| Entry Title | The title of the unused entry |
| Entry URL | The URL of the unused entry |
| Collection | The collection handle |

## Use Cases

- **Content Cleanup**: Identify unused assets and entries that can be safely removed
- **Content Audit**: Track which global blocks or reusable content is actually being used
- **Performance Optimization**: Remove unused assets to reduce storage and improve site performance
- **Content Strategy**: Understand content usage patterns to inform future content decisions
- **Migration Planning**: Identify orphaned content before migrating or archiving

## How It Works

The addon scans content sources in your Statamic site and:

1. **Sources scanned**:
   - Entries
   - Globals (all localizations)
   - Navigation trees
   - Terms (all localizations)

2. **For Assets**: Extracts asset references from source data, including:
   - `assets::` prefixed IDs (e.g., `assets::main::image.jpg`)
   - Plain file paths that can be resolved to assets

3. **For Entries**: Extracts entry references from source data, including:
   - `entry::collection::id` format references
   - Plain UUID references that match entries in the tracked collection

The addon then generates comprehensive reports showing relationships between your content.

## License

This addon is licensed under the MIT license.
