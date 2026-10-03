(function () {
  'use strict';
  var target = document.getElementById('rw-admin-editor');
  var fallback = document.getElementById('rw-admin-fallback');
  var config = window.rwAdmin;
  if (!target || !fallback || !config || !window.wp || !wp.element || !wp.components) return;
  var h = wp.element.createElement;
  var c = wp.components;
  if (!c.Card || !c.CardBody || !c.ToggleControl || !c.TextControl || !c.TextareaControl || !c.Button) return;
  var text = config.strings;
  function card(title, children) {
    return h(c.Card, { className: 'rw-editor-card' }, h(c.CardBody, null,
      h('h2', { className: 'rw-section-title' }, title), children));
  }
  function Settings() {
    var enabledState = wp.element.useState(config.options.enabled);
    var dismissibleState = wp.element.useState(config.options.dismissible);
    var titleState = wp.element.useState(config.options.title);
    var messageState = wp.element.useState(config.options.message);
    wp.element.useLayoutEffect(function () {
      // Keep the ordinary WordPress form usable until the component tree mounts.
      fallback.remove();
      target.setAttribute('data-ready', 'true');
    }, []);
    var previewMessage = messageState[0].trim() ? messageState[0] : config.defaults.message;
    if (!dismissibleState[0] && previewMessage.replace(/\r\n?/g, '\n').trim() === config.defaults.message) previewMessage = config.requiredMessage;
    return h('div', { className: 'rw-settings-grid' },
      h('div', { className: 'rw-settings-controls' },
        card(text.visibility, h(wp.element.Fragment, null,
          h(c.ToggleControl, { label: text.enable, checked: enabledState[0], onChange: enabledState[1], __nextHasNoMarginBottom: true }),
          h('input', { type: 'hidden', name: 'recipe_warning_options[enabled]', value: enabledState[0] ? '1' : '0' }),
          h('p', { className: 'rw-muted' }, text.limits),
          h('div', { className: 'rw-dismissal-control' },
            h(c.ToggleControl, { label: text.dismissible, checked: dismissibleState[0], onChange: dismissibleState[1], help: text.dismissibleHelp, __nextHasNoMarginBottom: true }),
            h('input', { type: 'hidden', name: 'recipe_warning_options[dismissible]', value: dismissibleState[0] ? '1' : '0' })))),
        card(text.copy, h(wp.element.Fragment, null,
          h(c.TextControl, { id: 'recipe-warning-title', label: text.title, name: 'recipe_warning_options[title]', value: titleState[0], onChange: titleState[1], maxLength: 180, __next40pxDefaultSize: true }),
          h('label', { className: 'components-base-control__label rw-message-label', htmlFor: 'recipe-warning-message' }, text.message),
          h(c.TextareaControl, { id: 'recipe-warning-message', name: 'recipe_warning_options[message]', value: messageState[0], onChange: messageState[1], rows: 8, maxLength: 4000, help: text.help, __nextHasNoMarginBottom: true }))),
        h('div', { className: 'rw-save-row' }, h(c.Button, { variant: 'primary', type: 'submit', id: 'submit', __next40pxDefaultSize: true }, text.save))),
      h('aside', { className: 'rw-preview', 'aria-label': text.preview },
        h('h2', { className: 'rw-section-title' }, text.preview),
        h('p', { className: 'rw-muted' }, text.previewHelp),
        h(c.Card, { className: 'rw-preview-card' }, h(c.CardBody, null,
          h('p', { className: 'rw-preview-label' }, text.label),
          h('h3', null, titleState[0].trim() ? titleState[0] : config.defaults.title),
          h('p', { className: 'rw-preview-message' }, previewMessage),
          h('div', { className: 'rw-preview-action' }, text.copyLink),
          h('div', { className: 'rw-preview-action' }, text.bypass),
          dismissibleState[0] ? h('div', { className: 'rw-preview-action' }, text.proceed) : null))));
  }
  // The component library ships with WordPress. No external runtime is loaded.
  if (wp.element.createRoot) wp.element.createRoot(target).render(h(Settings));
  else wp.element.render(h(Settings), target);
}());
