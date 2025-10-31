# BP Group Finder - AI/Developer Training Guide

## Plugin Overview
BP Group Finder extends BuddyPress groups functionality with interest-based discovery. It allows users to tag groups with interests and search/filter groups accordingly.

## File Structure

```
bp-group-finder/
├── bp-group-finder.php          # Main plugin file
├── uninstall.php                # Uninstall cleanup
├── includes/
│   ├── class-bpgf-loader.php    # Plugin loader
│   ├── class-bpgf-i18n.php      # Internationalization
│   ├── class-bpgf-activator.php # Activation hooks
│   ├── class-bpgf-deactivator.php # Deactivation hooks
│   ├── taxonomies/
│   │   └── class-bpgf-taxonomy.php # Custom taxonomy setup
│   ├── admin/
│   │   ├── class-bpgf-settings.php # Admin settings page
│   │   ├── class-bpgf-metabox.php  # Group edit metabox
│   │   └── class-bpgf-trending-widget.php # Dashboard widget
│   ├── public/
│   │   ├── class-bpgf-directory.php # Groups directory modifications
│   │   └── class-bpgf-search.php    # Search functionality
│   └── api/
│       ├── class-bpgf-ajax-handler.php # AJAX endpoints
│       └── class-bpgf-rest-controller.php # REST API
├── templates/                   # Template files
│   ├── directory-filters.php    # Search/filter UI
│   ├── search-form.php          # Search form
│   ├── group-tags.php           # Group tags display
│   └── trending-tags-widget.php # Widget template
├── assets/
│   ├── css/
│   │   ├── admin/
│   │   │   └── bpgf-admin.css
│   │   └── public/
│   │       └── bpgf-public.css
│   └── js/
│       ├── admin/
│       │   └── bpgf-admin.js
│       └── public/
│           └── bpgf-directory.js
└── languages/                   # Translation files (not shown)
```

## Key Classes and Functions

### Core Classes

#### BPGF_Loader
- **File**: `includes/class-bpgf-loader.php`
- **Purpose**: Main plugin orchestrator using WordPress hooks
- **Key Methods**:
  - `add_action()`: Register actions
  - `add_filter()`: Register filters
  - `run()`: Execute all registered hooks

#### BPGF_Taxonomy
- **File**: `includes/taxonomies/class-bpgf-taxonomy.php`
- **Purpose**: Registers the 'group_interest' taxonomy
- **Hooks**: `init` for taxonomy registration

#### BPGF_Settings
- **File**: `includes/admin/class-bpgf-settings.php`
- **Purpose**: Admin settings page and options management
- **Key Methods**:
  - `sanitize_settings()`: Input sanitization callback
  - `register_settings()`: Register settings sections/fields

### AJAX/API Classes

#### BPGF_AJAX_Handler
- **File**: `includes/api/class-bpgf-ajax-handler.php`
- **Purpose**: Handle AJAX requests for search/autocomplete
- **Endpoints**:
  - `wp_ajax_bpgf_autocomplete`: Tag autocomplete (admin)
  - `wp_ajax_bpgf_search_groups`: Group search (public)
  - `wp_ajax_bpgf_tag_stats`: Tag statistics (public)

#### BPGF_REST_Controller
- **File**: `includes/api/class-bpgf-rest-controller.php`
- **Purpose**: REST API endpoints
- **Routes**:
  - `/bpgf/v1/groups`: Group search
  - `/bpgf/v1/tags`: Available tags

### Public Classes

#### BPGF_Directory
- **File**: `includes/public/class-bpgf-directory.php`
- **Purpose**: Modify groups directory with filters/search
- **Hooks**: `bp_before_directory_groups_content`

#### BPGF_Metabox
- **File**: `includes/admin/class-bpgf-metabox.php`
- **Purpose**: Add interest tags metabox to group admin
- **Hooks**: `bp_group_admin_ui_edit_tabs`

## Data Storage

### Options
- `bpgf_db_version`: Plugin version
- `bpgf_max_tags_per_group`: Tag limit per group
- `bpgf_min_tag_length`: Minimum tag length
- `bpgf_max_tag_length`: Maximum tag length
- `bpgf_settings`: Main settings array

### Taxonomy
- **Name**: `group_interest`
- **Object Type**: Groups (via `bp_groups` table relationship)
- **Term Meta**: Usage counts, trending data

### Transients
- `bpgf_trending_tags_{period}`: Cached trending tags
- `bpgf_popular_tags`: Cached popular tags

## Hooks and Filters

### Actions
- `bpgf_before_search`: Before performing search
- `bpgf_after_search`: After search results
- `bpgf_group_tags_updated`: When group tags change

### Filters
- `bpgf_search_query_args`: Modify search query arguments
- `bpgf_autocomplete_results`: Filter autocomplete suggestions
- `bpgf_trending_tags`: Modify trending calculation

## Testing Commands

### PHPCS
```bash
phpcs --standard=WordPress /path/to/plugin
```

### PHPUnit
```bash
# If tests exist
phpunit
```

### Manual Testing
1. Activate plugin
2. Create groups with tags
3. Test search functionality
4. Check AJAX responses in browser dev tools

## Environment Variables
- `WP_DEBUG`: Enable for error logging
- `BPGF_DEBUG`: Plugin-specific debug mode

## Common Development Tasks

### Adding New Settings
1. Add field to `BPGF_Settings::register_settings()`
2. Add sanitization in `sanitize_settings()`
3. Add form field in render methods

### Extending Search
1. Hook into `bpgf_search_query_args` filter
2. Modify BP_Groups_Group query arguments

### Adding REST Endpoints
1. Extend `BPGF_REST_Controller`
2. Register routes in constructor
3. Implement callback methods

## Security Considerations
- All AJAX endpoints verify nonces
- User inputs sanitized with `sanitize_text_field()`
- Output escaped with `esc_*()` functions
- Settings validated with `absint()`, `in_array()` etc.

## Performance Notes
- Trending calculations cached with transients
- AJAX responses limited to prevent overload
- Taxonomy queries use appropriate limits
- Consider database indexes on term relationships</content>
</xai:function_call">Now create Enhancement.md with roadmap and improvements. 

<xai:function_call name="write">
<parameter name="filePath">/home/wbcomteam/Local Sites/buddypress-addons/app/public/wp-content/plugins/bp-group-finder/Enhancement.md