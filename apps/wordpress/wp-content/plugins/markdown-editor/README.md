# Markdown Editor

Replaces the default WordPress editor with a Markdown editor for your posts and pages. Add or remove Markdown support for post types by using `add_post_type_support` or `remove_post_type_support`. E.g:

```php
add_post_type_support( 'page', 'wpcom-markdown' );
```

### Syntax Highlighting

By default, Markdown Editor enables syntax highlighting for code blocks. This can be removed by adding the following line of code to your theme's functions.php file:

```php
add_filter( 'markdown_editor_highlight', '__return_false' );
```

The click to copy button can be removed with the following line:

```php
add_filter( 'markdown_editor_clipboard', '__return_false' );
```

## Screenshots

*Full Screen*
![Full screen](https://s3-us-west-1.amazonaws.com/seo-themes/wpmarkdown-fullscreen.png)

*Split Screen*
![Full screen](https://s3-us-west-1.amazonaws.com/seo-themes/wpmarkdown-splitscreen.png)