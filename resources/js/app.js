import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const setupMemberAutocomplete = () => {
    const inputs = document.querySelectorAll('[data-member-autocomplete]');

    inputs.forEach((input) => {
        const endpoint = input.dataset.autocompleteUrl;
        const form = input.closest('form');

        if (!endpoint || !form) {
            return;
        }

        const wrapper = input.closest('[data-member-autocomplete-wrapper]') ?? input.parentElement;

        if (!wrapper) {
            return;
        }

        wrapper.classList.add('position-relative');

        let abortController = null;
        let activeIndex = -1;
        let suggestions = [];

        const dropdown = document.createElement('div');
        dropdown.className = 'list-group shadow-sm position-absolute start-0 end-0 mt-1 d-none';
        dropdown.style.zIndex = '1050';
        dropdown.style.maxHeight = '280px';
        dropdown.style.overflowY = 'auto';
        wrapper.appendChild(dropdown);

        const closeDropdown = () => {
            dropdown.classList.add('d-none');
            dropdown.innerHTML = '';
            suggestions = [];
            activeIndex = -1;
        };

        const submitSearch = (value) => {
            input.value = value;
            closeDropdown();

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
                return;
            }

            form.submit();
        };

        const renderDropdown = () => {
            if (!suggestions.length) {
                closeDropdown();
                return;
            }

            dropdown.innerHTML = '';

            suggestions.forEach((suggestion, index) => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = `list-group-item list-group-item-action${index === activeIndex ? ' active' : ''}`;
                const title = document.createElement('div');
                title.className = 'fw-semibold';
                title.textContent = suggestion.full_name;

                const meta = document.createElement('div');
                meta.className = `small ${index === activeIndex ? 'text-white-50' : 'text-muted'}`;
                meta.textContent = `${suggestion.membership_id}${suggestion.phone ? ` · ${suggestion.phone}` : ''}`;

                item.appendChild(title);
                item.appendChild(meta);
                item.addEventListener('mousedown', (event) => {
                    event.preventDefault();
                    submitSearch(suggestion.search_value ?? suggestion.search_text);
                });

                dropdown.appendChild(item);
            });

            dropdown.classList.remove('d-none');
        };

        const fetchSuggestions = async (query) => {
            if (abortController) {
                abortController.abort();
            }

            abortController = new AbortController();

            try {
                const response = await fetch(`${endpoint}?q=${encodeURIComponent(query)}`, {
                    headers: {
                        Accept: 'application/json',
                    },
                    signal: abortController.signal,
                });

                if (!response.ok) {
                    closeDropdown();
                    return;
                }

                const payload = await response.json();
                suggestions = Array.isArray(payload.suggestions) ? payload.suggestions : [];
                activeIndex = suggestions.length ? 0 : -1;
                renderDropdown();
            } catch (error) {
                if (error.name !== 'AbortError') {
                    closeDropdown();
                }
            }
        };

        let debounceTimer = null;

        input.addEventListener('input', () => {
            const query = input.value.trim();

            if (debounceTimer) {
                window.clearTimeout(debounceTimer);
            }

            if (query.length < 1) {
                closeDropdown();
                return;
            }

            debounceTimer = window.setTimeout(() => {
                fetchSuggestions(query);
            }, 120);
        });

        input.addEventListener('focus', () => {
            const query = input.value.trim();
            if (query.length >= 1 && !suggestions.length) {
                fetchSuggestions(query);
            } else if (suggestions.length) {
                dropdown.classList.remove('d-none');
            }
        });

        input.addEventListener('keydown', (event) => {
            if (!suggestions.length) {
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                activeIndex = (activeIndex + 1) % suggestions.length;
                renderDropdown();
            }

            if (event.key === 'ArrowUp') {
                event.preventDefault();
                activeIndex = (activeIndex - 1 + suggestions.length) % suggestions.length;
                renderDropdown();
            }

            if (event.key === 'Enter' && activeIndex >= 0) {
                event.preventDefault();
                submitSearch(suggestions[activeIndex].search_value ?? suggestions[activeIndex].search_text);
            }

            if (event.key === 'Escape') {
                closeDropdown();
            }
        });

        document.addEventListener('click', (event) => {
            if (!wrapper.contains(event.target)) {
                closeDropdown();
            }
        });
    });
};

document.addEventListener('DOMContentLoaded', setupMemberAutocomplete);
