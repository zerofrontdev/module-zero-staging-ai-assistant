# Zero_StagingAiAssistant

The AI Assistant's tools for Zero_Staging: scheduling product and category changes for a period.

Admin only, like the assistant itself. A bridge module: Zero_AiAssistant
and Zero_Staging know nothing of each other, so a store that uses both installs
this one too.

## Tools

- `list_campaigns`: scheduled, live and completed campaigns with their changes, times in store time.
- `schedule_change`: a product (sku) or category change from a start to an end, in a new campaign or an existing scheduled one; shown to the admin to confirm first.

Each tool needs the same ACL resource the work needs in the admin (`Zero_Staging::staging`),
so a role without it never gets the tool. Zero_Staging is a licensed module: this bridge is licensed with it.

## How it plugs in

`etc/di.xml` adds the tools to the pool of `Zero\AiAssistant\Model\Tools`
(`\Proxy` items with a `sortOrder`, the order Claude and the chat see) and
names the module they need under `requires`: with Zero_Staging disabled they
are hidden and never built. There are no commands.
The tool classes take Zero_Staging's services as constructor arguments.

Disabling this module removes only these tools; the names, resources and
areas (`staging`) stay as they were when the tools lived in Zero_AiAssistant.

What changed in each release: [CHANGELOG.md](CHANGELOG.md).
