# Product images

These images show the actual CubePay interface with demo data; they are not records of customer payments.

- `android-reports.png`: unchanged screenshot of the Android reporting screen captured in the project emulator during UI validation. The connection-test row and delivery report use test fixtures. The displayed service shortcode is public configuration, not a customer number.
- `merchant-reports.png`: actual merchant web-panel HTML and report component rendered in Chromium at a mobile viewport. API responses are local demo fixtures, buyer rows are empty, and all external network requests are blocked.

No customer names, bank-card numbers, private webhook links, API tokens or Telegram identities are included. The README captions explicitly identify demo data. Regenerate images from the current UI when it changes; do not alter screenshot pixels to invent interface behavior.

The adjacent banner and payment-flow illustrations are original SVG assets, separate from the product screenshots. The flow uses a subtle CSS animation with reduced-motion support and remains readable without animation.
