# Reader UI review, 1.4.0

| Before | After |
| --- | --- |
| Single stack of similar actions | Header with document icon, separate settings-confirmation card, prominent Continue footer |
| Underlined Continue treatment | Full-width primary Continue action and accessible close control |
| One fixed dismissal behavior | WordPress toggle with dismissible default and optional required-confirmation mode |
| Same stock wording in every mode | Mode-aware stock copy and matching settings preview |
| Shared footer note | Seven-day explanation located beside the confirmation that saves it |

Native dialog retains keyboard containment, focus restoration, theme colors, contrast fallback and constrained scrolling. The new icon paths are original local source with decorative accessible semantics. No external UI runtime is loaded on reader pages. WordPress core components remain the admin design system.

Review sequence: normalize theme typography and controls; adapt mobile and short viewports; polish hierarchy and action grouping; clarify confirmation versus dismissal; harden Escape, close, storage and clipboard behavior in both modes; use a restrained document/check visual vocabulary. Screenshots document both modes.
