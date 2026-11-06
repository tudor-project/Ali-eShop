# Store Design Reference

## Active references

- Homepage: `references/konte/pages/home-v2.html`
- Shop and category pages: `references/konte/pages/shop-v2.html`
- Product pages: `references/konte/pages/product-v6.html`
- Global header: `references/konte/components/header-v2.html`
- Global footer: `references/konte/components/footer-v5.html`

Screenshots are the visual source of truth. HTML and CSS are supporting
references for spacing, typography, colors and component structure.

Reimplement the design using Blade, Tailwind CSS, Livewire and Alpine.
Do not copy Bootstrap classes or the reference JavaScript into the application.

## Design priorities

1. A page-specific client design in `client-pages/`
2. The current header and footer references
3. The corresponding Konte page reference
4. The fallback style in the Cursor rule

Client-specific designs override Konte only for the pages or components
explicitly named in their specification.

## Responsive behaviour

Use the reference as visual direction, not as a requirement to reproduce its
desktop implementation. Build mobile-first and preserve:

- Visual hierarchy
- Typography character
- Color palette
- Spacing rhythm
- Product-card style
- Image proportions
- Overall minimalist and premium appearance

Adapt layouts for accessibility and touch use where the original design does
not work well on mobile.

## Unspecified decisions

If a significant visual decision is not shown in a reference or client
specification, ask before introducing a new design pattern.

## Product cards

All product grids use:

- Structure and styling from Shop v2
- One neutral placeholder image until real product images are supplied

The hover behaviour applies only to pointer-capable devices. Mobile controls
must remain directly usable without hover.

# Product Card Behaviour

Reference: `active.html`
Source style: Konte Product Clasic

## Desktop

- Product image scales slightly when the pointer hovers over the card.
- Reveal product actions without shifting the card layout.
- Product name, price and image remain clickable.

## Mobile and touch

- Hover is an enhancement only.
- Do not require hover to access any action.
- Product actions must be visible or accessible by tapping the card/menu.
- Action targets must be at least 44×44px.
- Tapping the image opens the product page; it must not trigger hover first.

## Implementation

Reimplement with Tailwind CSS.
Use Alpine only if state is required.
Do not copy Bootstrap, jQuery or Konte JavaScript.
Respect `prefers-reduced-motion`.