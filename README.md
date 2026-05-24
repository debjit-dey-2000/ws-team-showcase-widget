# WS Team Showcase Plugin

Version: 1.0.0  
Author: Debjit Dey (WS01530)

## Description

WS Team Showcase Plugin adds a customizable Elementor widget for displaying team members in grid or table layouts with filters, popups, hover effects, dark mode, and AJAX Load More.

## Requirements

- WordPress
- Elementor
- Advanced Custom Fields, required for the automatic team post type fields

## Features

- Automatic Team Members post type when ACF is active
- Automatic Team Categories taxonomy when ACF is active
- Automatic ACF field group for team member details
- Elementor team showcase widget
- Grid and table layout toggle
- Category/department filtering
- Dark mode toggle
- Team member popup details
- AJAX Load More after 8 members
- Card hover effects: FlipBox and Content Reveal Zoom Blur
- Widget controls for typography, colors, spacing, borders, radius, and buttons
- Social links for LinkedIn, Facebook, and Instagram

## Installation

1. Upload the `ws-team-showcase-pro` folder to `wp-content/plugins/`.
2. Go to WordPress Admin > Plugins.
3. Activate `WS Team Showcase Plugin`.
4. Edit a page with Elementor.
5. Search for `WS Team Showcase` and add the widget to the page.

## Team Member Content

When Advanced Custom Fields is active, the plugin automatically registers:

- `Team Members` post type (`ws_team`)
- `Team Categories` taxonomy (`team_category`)
- `Team Member Details` ACF field group

The generated ACF fields are:

- `designation`
- `experience`
- `linkedin`
- `facebook`
- `instagram`

The post title is used as the team member name, the featured image is used as the profile image, and the post content is used for the popup and short bio.

If ACF is not active, the plugin will not register the Team Members post type, taxonomy, or field group. Activate ACF first, then activate or reload this plugin.

## Widget Settings

In the widget Content tab, you can configure:

- Post type
- Order by
- Order
- Filter visibility
- Dark mode toggle
- Layout toggle
- Card hover content effect

In the Style tab, you can customize:

- Layout columns and spacing
- Card box, image, and content styling
- Heading, designation, experience, bio, and social icons
- View Details button
- Load More button
- Popup box, overlay, close button, and popup content

## AJAX Load More

The widget shows the first 8 team members by default. If more than 8 members exist, a Load More button appears automatically and loads the next set of members without refreshing the page.

## Notes

- Regenerate Elementor CSS after major style changes if old styles are cached.
- Replace the plugin URI and author URI in `ws-team-showcase-widget.php` with the final plugin site link.
