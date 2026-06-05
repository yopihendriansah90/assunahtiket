---
name: Professional Scanner Interface
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#45464d'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#76777d'
  outline-variant: '#c6c6cd'
  surface-tint: '#565e74'
  primary: '#000000'
  on-primary: '#ffffff'
  primary-container: '#131b2e'
  on-primary-container: '#7c839b'
  inverse-primary: '#bec6e0'
  secondary: '#0051d5'
  on-secondary: '#ffffff'
  secondary-container: '#316bf3'
  on-secondary-container: '#fefcff'
  tertiary: '#000000'
  on-tertiary: '#ffffff'
  tertiary-container: '#002113'
  on-tertiary-container: '#009668'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dae2fd'
  primary-fixed-dim: '#bec6e0'
  on-primary-fixed: '#131b2e'
  on-primary-fixed-variant: '#3f465c'
  secondary-fixed: '#dbe1ff'
  secondary-fixed-dim: '#b4c5ff'
  on-secondary-fixed: '#00174b'
  on-secondary-fixed-variant: '#003ea8'
  tertiary-fixed: '#6ffbbe'
  tertiary-fixed-dim: '#4edea3'
  on-tertiary-fixed: '#002113'
  on-tertiary-fixed-variant: '#005236'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  display-lg:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.01em
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.02em
  mono-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
    letterSpacing: 0.05em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  base: 4px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  gutter: 16px
  margin: 16px
---

## Brand & Style
The design system is engineered for high-performance utility, speed, and absolute reliability. It targets event staff and logistics professionals who require a tool that functions flawlessly under pressure and in varied environmental conditions—from bright outdoor sunlight to dim concert halls.

The aesthetic follows a **Corporate / Modern** direction with a focus on **High-Contrast** accessibility. It prioritizes functional clarity over decorative flair, ensuring that the primary action—validating a ticket—is the undisputed focal point of the user experience. The emotional response is one of confidence, precision, and authority.

## Colors
This design system utilizes a high-contrast palette to ensure legibility. 
- **Primary (Deep Navy):** Used for core structural elements, headers, and primary text to establish authority.
- **Secondary (Action Blue):** Reserved for interactive elements like buttons and active states.
- **Status Colors:** Success Green and Error Red are optimized for maximum visibility against white backgrounds to provide instant visual feedback during the scanning process.
- **Neutrals:** A range of slate grays provides hierarchy without introducing visual noise.

## Typography
The system uses **Inter** for its exceptional legibility and systematic feel.
- **Headlines:** Use Bold and Semi-Bold weights to create a strong information hierarchy.
- **Numerical Data:** High-priority numbers (like guest counts) should use `headline-md` for immediate recognition.
- **Labels:** Uppercase styles are permitted for small labels (`label-sm`) to distinguish metadata from body content.
- **Scale:** All typography is optimized for mobile viewing, ensuring that even at arms-length, a scanner operator can read ticket statuses clearly.

## Layout & Spacing
This design system utilizes a **Fixed Grid** model for mobile devices, based on a 4-column structure.
- **Margins & Gutters:** A standard 16px margin provides a safe area for one-handed thumb operation. Gutters are fixed at 16px to maintain clear separation between interactive elements.
- **Rhythm:** A 4px baseline grid governs all vertical spacing. Elements are spaced in multiples of 8px (e.g., 16px between list items, 32px between sections).
- **Safe Areas:** Special attention is given to bottom-heavy layouts to accommodate modern mobile gesture bars and ensure buttons are within the "natural" thumb zone.

## Elevation & Depth
Depth is conveyed through **Tonal Layers** and **Low-Contrast Outlines** rather than heavy shadows. This maintains a clean, digital-first look that performs better in high-glare environments.
- **Level 0 (Base):** Pure white background for maximum contrast.
- **Level 1 (Surface):** Subtle light gray (#F8FAFC) used for card containers and search bars to separate them from the background.
- **Borders:** 1px solid borders in a soft neutral (#E2E8F0) replace shadows for a more structured, professional appearance.
- **Overlay:** Semi-transparent dark overlays (80% opacity) are used only for camera viewfinders to focus the user's attention on the scanning target.

## Shapes
The design system adopts a **Soft** shape language. 
- **Standard Radius:** 4px (0.25rem) for buttons, input fields, and small UI components. This provides a professional, "tooled" look.
- **Large Radius:** 8px (0.5rem) for cards and modal containers.
- **Full Radius:** Reserved exclusively for selection chips and status badges to make them instantly distinguishable from primary action buttons.

## Components
### Camera Viewfinder
The viewfinder should occupy the upper 50-60% of the screen. It features a high-contrast white "corner-bracket" overlay. Upon a successful scan, the entire viewfinder frame should flash green; on error, it should flash red.

### Search Inputs
Inputs use a white background with a 1px slate border. The search icon is placed on the left, and a "clear" action on the right. Focus states use a 2px blue border.

### Selection Chips
Chips are used for gate or event selection.
- **Inactive:** Outlined with neutral text.
- **Active:** Solid primary (navy) background with white text for maximum prominence.

### List Items (History)
Compact rows with a clear status indicator (dot or icon) on the left. The guest name is `body-md`, while the timestamp and ticket ID use `label-sm` in a muted neutral color.

### Buttons
Primary buttons are high-contrast navy with white text. Secondary buttons are outlined. All buttons must have a minimum touch target height of 48px to ensure accessibility for staff wearing gloves or moving quickly.