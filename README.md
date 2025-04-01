# Printables Embed

A Drupal 10 module that provides a custom field type for embedding 3D models from Printables.com.

## Description

This module allows you to easily embed 3D models from Printables.com into your Drupal content.
It creates a custom field type that accepts Printables URLs and displays them as rich embeds
similar to the official Printables embed iframe.

Features:
- Custom field type for Printables URLs
- Custom formatter with configurable width/height
- Caching of API responses for improved performance
- Responsive design that matches the official embed style

## Requirements

- Drupal 10
- Field module
- HTTP Client (Guzzle)

## Installation

1. Download the module to your site's modules/custom directory:
   ```
   cd /path/to/drupal/modules/custom
   git clone https://github.com/CharlesJGantt/Printables_Embed_Test.git printables_embed
   ```

2. Enable the module using Drush:
   ```
   drush en printables_embed -y
   ```

3. Clear cache:
   ```
   drush cr
   ```

## Configuration

### Adding the field to a content type

1. Go to Structure > Content types > [Your content type] > Manage fields
2. Click "Add field"
3. From the "Add a new field" dropdown, select "Printables Embed"
4. Enter a label for the field (e.g., "Printables Model")
5. Click "Save and continue"
6. Configure field settings as needed
7. Click "Save settings"

### Configuring the display

1. Go to Structure > Content types > [Your content type] > Manage display
2. Find your Printables field and configure format settings
3. Click the gear icon to adjust width and height settings
4. Click "Update" and then "Save"

## Usage

When creating or editing content with a Printables Embed field:

1. Enter a valid Printables URL in one of these formats:
   - https://www.printables.com/model/172969
   - https://www.printables.com/embed/172969

The module will automatically:
- Extract the model ID from the URL
- Fetch model data from the Printables GraphQL API
- Generate a styled embed that displays the model image, name, author, and stats
- Cache the results to improve performance

## How It Works

The module uses the Printables GraphQL API to fetch model data including:
- Model name and image
- Author name and avatar
- Like, download, and view counts

This data is then displayed in a styled container that matches the look and feel of the official Printables embed.

## Credits

- Original GraphQL implementation inspired by [Elias Ruemmler's PrintablesGraphQL](https://github.com/eliasruemmler/printablesgraphql)
- Created by Charles J Gantt

## License

This project is licensed under the MIT License - see the LICENSE file for details.