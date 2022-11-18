# Cloudflare’s Plugin for WordPress

![build status](https://github.com/cloudflare/Cloudflare-WordPress/workflows/PHP%20Composer/badge.svg)

Cloudflare’s WordPress plugin brings all of the benefits of Cloudflare into your WordPress dashboard for configuration, including a one-click application of default settings specifically optimized for WordPress.

By enabling Cloudflare on your WordPress website, you’ll find performance and security gains such as doubling your page load speeds, DDoS protection, web application firewall with WordPress-specific rulesets, free SSL, and SEO improvements.

## Table of Contents

[Description](#description)  
[Installation](#installation)  
[Support](#support)  
[Features](#features)  
[FAQ](#faq)  
[Contributions](#contributions)  
[Changelog](#changelog)  

## Description

The WordPress plugin for Cloudflare offers all of the benefits of Cloudflare, with a one-click installation of settings specifically developed for the WordPress platform. Cloudflare’s free plugin for WordPress accelerates page load speeds, improves SEO, and protects against DDoS attacks and WordPress-specific vulnerabilities.

## Features

### One-click WordPress-optimized settings

One-click application of WordPress-optimized settings is the easiest way to setup Cloudflare’s performance and security on your WordPress site. You can review the recommended settings that are applied, here: <https://support.cloudflare.com/hc/en-us/articles/227342487>

### Web application firewall (WAF) rulesets

Cloudflare’s web application firewall (WAF), available on all of Cloudflare’s paid plans, has built-in rulesets specifically built to mitigate against WordPress threats and vulnerabilities. Cloudflare’s WAF provides confidence that your website is always protected, even against the latest threats and vulnerabilities targeting WordPress websites.

### Automatic cache purge on website updates

Cloudflare’s plugin for WordPress automatically refreshes the Cloudflare cache upon making changes to your website appearance. This means that you can focus on your website, while we take care of ensuring that the freshest content is always available to your visitors.

### Automatic individual URL cache purge on page/post/custom post type edits

Cloudflare’s plugin for WordPress automatically refreshes the Cloudflare cache of each post/page/custom post type when you update the content.

### Additional Features

- Header rewrites to prevent a redirect loop when Cloudflare’s Universal SSL is enabled.
- Change Cloudflare settings from within the plugin without needing to navigate to the Cloudflare.com dashboard. You can change settings for cache purge, security level, Always Online™, and image optimization.
- View analytics such as total visitors, bandwidth saved, and threats blocked.
- Support for HTTP2/Server Push.

## Installation

1. To install the Cloudflare plugin into your WordPress admin panel, please visit: <https://wordpress.org/plugins/cloudflare/> and click “Download” or download the plugin from this GitHub repo.
    - To install the plugin after downloading it via GitHub, navigate to Plugins → Add New → Upload Plugin and upload the Cloudflare-WordPress-master.zip file.
2. Once you’ve installed the plugin, activate it through the WordPress plugin panel.
3. If you're upgrading from the old plugin, and had previously inputted your API Key, you'll be automatically logged-in after updating the plugin.
4. If this is the first time installing Cloudflare's WordPress plugin, please navigate to the plugin settings inside of your WordPress admin panel after activating, and input your Cloudflare username and API key; to find your API key, follow these instructions. If you do not already have a Cloudflare account, you’ll see the option to create one.
5. After successfully logging into the plugin, click the “Apply Default Settings” button within the plugin’s dashboard. Clicking “Apply” will enable specific Cloudflare settings, optimized for the WordPress platform. These settings can be found here.
6. Once this setting has been applied, that’s it! Your website is now on the Cloudflare network and optimized for the WordPress platform. You’ll also begin to see improved website load speeds, bandwidth savings, and protection against hackers, spammers, and bots.

## FAQ

### Do I need a Cloudflare account to use the plugin?

Yes, on install and activation the plugin, first time users will be asked to enter their email address (used to sign-up for an account at cloudflare.com) and their user API key. This is needed to support all the features offered by the plugin.

### What settings are applied when I click "Apply Default Settings" in Cloudflare's WordPress plugin?

 You can review the recommended settings that are applied, here: <https://support.cloudflare.com/hc/en-us/articles/227342487>

### Does the plugin work if I have Varnish enabled?

Yes, Cloudflare works with, and helps speed up your site even more, if you have Varnish enabled.

## Support

### Visit Our Knowledge Base

Before submitting a support ticket, check out our knowledge base to see if your question has already been answered: <https://support.cloudflare.com/hc/en-us/sections/200820268-Content-Management-System-CMS->

### File a Support Ticket

For all support inquiries regarding Cloudflare’s WordPress plugin, please login to your Cloudflare account, file a support ticket, include any screenshots or details: <https://support.cloudflare.com/hc/en-us/requests/new>

## Contributions

We welcome community contribution to this repository. [CONTRIBUTING.md](CONTRIBUTING.md) will help you start contributing. You can find active problems to work on in [Issues](https://github.com/cloudflare/Cloudflare-WordPress/issues) page.

## Changelog

See [changelog](https://wordpress.org/plugins/cloudflare/changelog/)
