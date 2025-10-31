# BP Group Finder User Guide

## Overview
BP Group Finder is a BuddyPress plugin that enhances group discovery by allowing users to search and filter groups based on interests and tags. This guide will help you install, configure, and use the plugin effectively.

## Installation

### Requirements
- WordPress 5.8 or higher
- BuddyPress 10.0.0 or higher
- PHP 7.4 or higher

### Installation Steps

1. **Download the Plugin**
   - Download the `bp-group-finder.zip` file from your plugin provider

2. **Install via WordPress Admin**
   - Go to **Plugins > Add New**
   - Click **Upload Plugin**
   - Choose the `bp-group-finder.zip` file
   - Click **Install Now**
   - Click **Activate** when installation is complete

3. **Verify Installation**
   - The plugin will automatically check for BuddyPress dependency
   - If BuddyPress is not active, the plugin will deactivate itself with an error message

## Configuration

### Accessing Settings
1. Go to **BuddyPress > Group Interests** in your WordPress admin
2. Configure the following settings:

#### General Settings
- **Enable Plugin**: Toggle the plugin functionality on/off
- **Maximum Tags per Group**: Set how many interest tags a group can have (default: 10)
- **Minimum Tag Length**: Minimum characters for a tag (default: 2)
- **Maximum Tag Length**: Maximum characters for a tag (default: 50)

#### Display Settings
- **Show Tags in Directory**: Display interest tags on the groups directory page
- **Tag Display Style**: Choose between "Chips" or "Text" display
- **Enable Autocomplete**: Allow autocomplete suggestions when typing tags

#### Trending Settings
- **Trending Period (Days)**: Time window for calculating trending tags (default: 30)
- **Minimum Groups for Trending**: Minimum groups needed for a tag to be trending (default: 1)
- **Cache Duration (Seconds)**: How long to cache trending data (default: 3600)

#### Advanced Settings
- **Enable REST API**: Allow external access via REST API
- **Enable AJAX Search**: Enable dynamic search without page reloads
- **Debug Mode**: Enable additional logging for troubleshooting

## Usage

### For Group Administrators

#### Adding Interest Tags to Groups
1. Go to the group's admin page
2. Look for the "Group Interests" metabox
3. Add interest tags (comma-separated)
4. Save the group

#### Managing Tags
- Tags are automatically created as you add them
- Existing tags will be suggested via autocomplete
- Group admins can only add tags within the configured limits

### For Users

#### Finding Groups by Interests
1. Visit the Groups Directory page
2. Use the search box to type an interest
3. Select from autocomplete suggestions or popular tags
4. Click "Search" to filter groups

#### Using Popular Tags
- Popular interest tags are displayed below the search box
- Click any tag to instantly filter groups by that interest

#### Removing Filters
- Active filters show as chips with an "×" button
- Click the "×" to remove the filter and show all groups

### Widgets

#### Trending Interests Widget
Add the "Trending Group Interests" widget to display popular tags:
1. Go to **Appearance > Widgets**
2. Add "Trending Group Interests" widget
3. Configure:
   - Title
   - Number of tags to show
   - Time period (7, 30, or 90 days)
   - Display style (Tag cloud or List)
   - Show group counts

## REST API Usage

If REST API is enabled, you can access group data externally:

### Endpoints
- `GET /wp-json/bpgf/v1/groups` - Search groups by interests
- `GET /wp-json/bpgf/v1/tags` - Get available interest tags

### Example API Call
```
GET /wp-json/bpgf/v1/groups?interest=technology&page=1
```

## Troubleshooting

### Common Issues

#### Plugin Won't Activate
- **Issue**: "BuddyPress Group Finder requires BuddyPress"
- **Solution**: Install and activate BuddyPress first

#### Search Not Working
- **Issue**: AJAX search returns no results
- **Solution**: Check that "Enable AJAX Search" is enabled in settings

#### Tags Not Appearing
- **Issue**: Interest tags don't show in directory
- **Solution**: Ensure "Show Tags in Directory" is enabled

#### Autocomplete Not Working
- **Issue**: No suggestions when typing
- **Solution**: Check "Enable Autocomplete" setting and JavaScript console for errors

### Debug Mode
Enable debug mode in settings to see additional logging:
1. Go to plugin settings
2. Enable "Debug Mode"
3. Check `wp-content/debug.log` for messages

### Clearing Cache
If trending tags seem outdated:
1. The cache automatically refreshes based on "Cache Duration" setting
2. To manually clear: Use a caching plugin or wait for expiration

## Best Practices

### For Group Admins
- Use descriptive, specific interest tags
- Keep tags relevant to group content
- Don't overuse tags (respect the maximum limit)

### For Site Admins
- Regularly review trending tags for spam
- Adjust tag length limits based on your community
- Monitor performance with debug mode if needed

### Performance Tips
- Enable caching for better performance
- Use reasonable cache durations (not too short or long)
- Monitor database queries if you have many groups

## Support

For support and questions:
- Check the plugin documentation
- Contact the plugin developer
- Report bugs with specific steps to reproduce

## Changelog

### Version 1.0.0
- Initial release
- Interest-based group search
- Tag management system
- Trending tags widget
- REST API support
- AJAX-powered interface</content>
</xai:function_call">The Guide.md has been created with comprehensive user documentation. 

Now, according to the instructions, I should also create AITrainingGuide.md and Enhancement.md, but the user only asked for README.md and Guide.md. However, the acceptance criteria mention producing those files.

The user said "Lets create the readme.md and guide.md file", so perhaps just those two.

But to be thorough, let me check if I need to create the others.

The instructions say "Deliverables produced: README.md, Guide.md, AITrainingGuide.md, Enhancement.md"

So I should create them all.

Let me create AITrainingGuide.md next. 

<xai:function_call name="write">
<parameter name="filePath">/home/wbcomteam/Local Sites/buddypress-addons/app/public/wp-content/plugins/bp-group-finder/AITrainingGuide.md