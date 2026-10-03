# WordPress.org submission audit

Reviewed October 3, 2026. Product: **Recipe AI Summary Notice for Firefox**, developed by And/or Labs Inc. Intended contributor: `vj1987`. Intended directory slug: `recipe-ai-summary-notice`.

The source review found no code-level violation of the requirements below. This is an engineering assessment, not a WordPress.org approval or a guarantee of legal compliance. Directory acceptance remains a manual review decision.

## Official sources

- [Detailed Plugin Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/)
- [Plugin Developer FAQ](https://developer.wordpress.org/plugins/wordpress-org/plugin-developer-faq/)
- [Submission page](https://wordpress.org/plugins/developers/add/)
- [Compliance disclaimer guidance](https://developer.wordpress.org/plugins/wordpress-org/compliance-disclaimers/)

## Guideline evidence

Numbers correspond to the detailed guidelines linked above. Evidence describes the reviewed implementation rather than predicting review outcomes.

| Guideline | Evidence in this plugin |
| --- | --- |
| 1. Licensing | PHP header and readme declare GPL version 2 or later. The complete GPLv2 text accompanies the software in `LICENSE.txt`. No bundled third-party libraries, fonts, or logos. |
| 2. Developer responsibility | Source and archive are inspectable; And/or Labs Inc. is identified as author. Security controls are described below. Account ownership still requires verification. |
| 3. Directory release | Complete installable archive exists. After approval, publish and maintain the actual release in WordPress.org SVN as well as GitHub. GitHub alone is not a directory release. |
| 4. Readable source | PHP, JavaScript, and CSS ship as readable source. Readme links the public repository. Packaging script is available in the repository. |
| 5. Trial restrictions | All functionality is available without payment, accounts, quotas, or license keys. |
| 6. Services | No remote service dependency or API subscription. News links are references, not runtime services. |
| 7. Privacy | No analytics, telemetry, cookies, or outbound API calls. Explicit visitor confirmation writes a local expiry timestamp; its purpose, duration, removal, and retention are disclosed. |
| 8. Remote execution | All executable assets ship locally. No remote updater, installer, downloaded code, iframe, or executable AI output. |
| 9. Honest behavior | Notice does not claim to block summaries, verify browser settings, test recipes, or guarantee legal protection. Default copy makes no claim that the publisher tested its recipe. Don Marti is credited for the idea without implying endorsement. |
| 10. Public credits | Visitor notice contains no external credit or marketing link. Source references and attribution are in documentation and the plugin settings panels. The copy control copies the current recipe URL. |
| 11. Admin behavior | Native Settings submenu, scoped panels, standard settings form, and editor checkbox. No activation redirect, dashboard takeover, advertising, or persistent nag. |
| 12. Directory presentation | Readme describes actual behavior, limitations, and three relevant tags. No affiliate links, review incentives, or keyword stuffing. |
| 13. Libraries | Native browser APIs and WordPress APIs only. No replacement copies of WordPress libraries. |
| 14. SVN use | Development occurs on GitHub. Directory SVN is reserved for deliberate releases after acceptance. |
| 15. Versions | Rename is a new 1.2.0 release. Header, assets, readme stable tag, and archive version must agree in the packaged release. |
| 16. Complete submission | The distributable contains the PHP entry point, local CSS/JS, readme, and license. Tests, local sites, dependencies, and logs remain outside it. |
| 17. Names and trademarks | Firefox is descriptive at the end of the display name, not the initial brand. Intended slug contains no third-party trademark. Independence and trademark attribution are explicit. Slug availability and acceptance remain unconfirmed. |
| 18. Review discretion | No claim of preapproval. Reviewer decisions and future guideline changes still apply. |

## Security and behavior inspection

- Direct PHP access exits unless WordPress is loaded.
- Settings use `register_setting`, a sanitization callback, `settings_fields`, and the native `options.php` handler. Settings rendering requires `manage_options`.
- Editor metadata writes require both a verified nonce and `edit_post` capability; autosaves and revisions are excluded.
- Publisher text is sanitized and bounded to 180 title characters and 4,000 message characters. HTML output uses context-appropriate escaping. JavaScript inserts publisher text through `textContent`, not HTML parsing.
- Inline configuration uses `wp_json_encode` with HTML-sensitive characters encoded.
- Read-only tab selection is sanitized and restricted to a fixed list; it performs no mutation.
- Recipe scanning has explicit script, size, and node limits. Malformed metadata, unavailable storage, and unavailable dialog APIs leave the original content available.
- No custom SQL, remote request handler, upload endpoint, REST route, arbitrary code editor, or shell execution exists in the plugin.

See [TESTING.md](../TESTING.md) for executed checks and boundaries. The final 1.2.0 ZIP contains five runtime/license/readme files and no development dependencies or credentials. Every archived file matched source and the installed renamed plugin byte-for-byte. WordPress Plugin Check reported no errors (`artifacts/plugin-check-1.2.0.txt`); all 57 PHP release assertions and the focused browser/content checks passed.

## Account and publication gates

1. **Company association.** The official FAQ's [organization-account guidance](https://developer.wordpress.org/plugins/wordpress-org/plugin-developer-faq/#how-do-i-submit-an-official-plugin) requires an account clearly associated with the represented organization. It identifies the account email as important evidence. `vj1987` can remain the contributor, but its company association and reachable email must be verified before representing it as the official And/or Labs submission account. A public author label alone does not establish that association. Do not publish the account email in this repository.
2. **Authentication and review.** Upload through the authenticated submission page. Record its actual receipt and review state. A GitHub release, a successful local installation, and an automated Plugin Check pass do not mean directory submission or approval.
3. **Actual slug.** WordPress.org derives a proposed slug from the submitted display name. Confirm or request `recipe-ai-summary-notice` through the offered pre-review slug change, then align the installed folder and translation text domain with the accepted slug. Do not advertise an unassigned directory URL as live.
4. **After approval.** Publish source to the assigned SVN release structure and maintain matching stable tags. Keep contact details reachable for review and security reports. Avoid duplicate submissions for the same plugin.

The submission FAQ also calls for a production-ready ZIP below 10 MB without development files. Account and directory facts above cannot be inferred from the local code review.
