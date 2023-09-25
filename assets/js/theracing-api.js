'use strict';

const accordionsModule = () => {
    const accordions = Array.prototype.slice.call(document.querySelectorAll('[data-component="accordion"]'));

    const accordionOpen = (blockAccordionItems, accordionSingle) => {
        blockAccordionItems.map((accordionItem, i) => {
            const title = accordionItem.querySelector('.racecard-head');

            title.addEventListener('click', () => {
                accordionSingle && (blockAccordionItems.map((item, j) => { i !== j && item.setAttribute('aria-expanded', 'false') }));
                accordionItem.setAttribute('aria-expanded', accordionItem.getAttribute('aria-expanded') === 'true' ? 'false' : 'true');
            });
        });
    };

    accordions.map((accordion) => {
        const blockAccordionItems = Array.from(accordion.querySelectorAll('.theracing-api'));
        accordionOpen(blockAccordionItems, accordion.dataset.accordion === 'single');
    });
};


accordionsModule();