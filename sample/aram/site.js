const menuButton = document.querySelector('.nav-toggle');
const menu = document.querySelector('#site-nav');
const submenuButtons = [...document.querySelectorAll('.sub-toggle')];

menuButton.addEventListener('click', () => {
    const expanded = menuButton.getAttribute('aria-expanded') === 'true';
    menuButton.setAttribute('aria-expanded', String(!expanded));
    if (expanded) {
        submenuButtons.forEach(button => button.setAttribute('aria-expanded', 'false'));
    }
});

submenuButtons.forEach(button => {
    button.addEventListener('click', () => {
        const expanded = button.getAttribute('aria-expanded') === 'true';
        submenuButtons.forEach(other => other.setAttribute('aria-expanded', 'false'));
        button.setAttribute('aria-expanded', String(!expanded));
    });
});

document.querySelector('.site-header').addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;

    const openSubmenu = menu.querySelector('.sub-toggle[aria-expanded="true"]');
    if (openSubmenu) {
        openSubmenu.setAttribute('aria-expanded', 'false');
        openSubmenu.focus();
    } else if (menuButton.getAttribute('aria-expanded') === 'true') {
        menuButton.setAttribute('aria-expanded', 'false');
        menuButton.focus();
    } else if (menu.contains(document.activeElement)) {
        document.activeElement.blur();
    }
});
