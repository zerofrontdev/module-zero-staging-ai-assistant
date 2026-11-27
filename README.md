# Zero_StagingAiAssistant

The AI Assistant's tools for Zero_Staging: scheduling product and category changes for a period.

Admin only, like the assistant itself. A bridge module: Zero_AiAssistant
and Zero_Staging know nothing of each other, so a store that uses both installs
this one too.

## Install

Requires Magento Open Source 2.4.7–2.4.9 and PHP 8.2–8.4 with the GD extension built with WebP (`php -r 'var_dump(function_exists("imagewebp"));'` says `true`; Magento's own PHP requirements cover the rest). Without WebP in GD, Zero skips its WebP image copies and serves the originals.

```bash
composer require zerofront/module-zero-staging-ai-assistant
bin/magento setup:upgrade
bin/magento setup:di:compile           # production mode
bin/magento setup:static-content:deploy # production mode
bin/magento cache:flush
```

Composer installs `zerofront/module-zero-ai-assistant`, `zerofront/module-zero-staging` with it.

Part of [`zerofront/zero`](https://zerofront.dev), which installs the Zero theme and every Zero module at once.

## Tools

- `list_campaigns`: scheduled, live and completed campaigns with their changes, times in store time.
- `schedule_change`: a product (sku) or category change from a start to an end, in a new campaign or an existing scheduled one; shown to the admin to confirm first.

Each tool needs the same ACL resource the work needs in the admin (`Zero_Staging::staging`),
so a role without it never gets the tool. Zero_Staging is free and so is this bridge (`composer require zerofront/module-zero-staging-ai-assistant`).

## How it plugs in

`etc/di.xml` adds the tools to the pool of `Zero\AiAssistant\Model\Tools`
(`\Proxy` items with a `sortOrder`, the order Claude and the chat see) and
names the module they need under `requires`: with Zero_Staging disabled they
are hidden and never built. There are no commands.
The tool classes take Zero_Staging's services as constructor arguments.

Disabling this module removes only these tools; the names, resources and
areas (`staging`) stay as they were when the tools lived in Zero_AiAssistant.

What changed in each release: [CHANGELOG.md](CHANGELOG.md).

## License

OSL-3.0 or AFL-3.0, your choice: see `NOTICE.md`.

---

Zero is made by [Fixstacks](https://fixstacks.com).
Documentation: https://zerofront.dev/docs. Demo store: https://demo.zerofront.dev.
