/**
 * ASDA Member Management System (MMS)
 * Full Stack Developers: Dhanushka Bandara, Greshan Bandara
 * Attribution: AUTHORS / CREDITS.md (not shown in the UI)
 */

import './bootstrap';
import Chart from 'chart.js/auto';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

const normalizeNic = (value) => {
    let nic = String(value || '')
        .toUpperCase()
        .replace(/[\s\-_./]/g, '');

    nic = nic.replace(/[^0-9VX]/g, '');

    // Keep only one trailing letter for old NIC format.
    const letterMatch = nic.match(/^(\d{0,12})([VX]?).*$/);
    if (!letterMatch) {
        return '';
    }

    let digits = letterMatch[1].slice(0, 12);
    let letter = letterMatch[2] || '';

    if (digits.length > 9 && letter) {
        // New NIC is digits-only; drop letter if user typed past 9 digits.
        if (digits.length >= 12) {
            letter = '';
            digits = digits.slice(0, 12);
        }
    }

    if (digits.length === 9 && letter) {
        return digits + letter;
    }

    if (digits.length > 9) {
        return digits.slice(0, 12);
    }

    return digits + letter;
};

const normalizePhone = (value) => {
    let phone = String(value || '').trim();

    if (!phone) {
        return '';
    }

    // Keep digits; allow a leading + while typing.
    phone = phone.replace(/[^\d+]/g, '');

    if (phone.startsWith('+')) {
        phone = phone.slice(1);
    }

    if (phone.startsWith('94') && phone.length >= 11) {
        phone = '0' + phone.slice(2);
    }

    phone = phone.replace(/\D/g, '');

    if (phone && !phone.startsWith('0') && phone.length === 9) {
        phone = '0' + phone;
    }

    return phone.slice(0, 10);
};

const bindAutoCorrect = (selector, formatter, { live = true } = {}) => {
    document.querySelectorAll(selector).forEach((input) => {
        const apply = () => {
            const corrected = formatter(input.value);
            if (input.value !== corrected) {
                const start = input.selectionStart;
                const end = input.selectionEnd;
                const prevLength = input.value.length;
                input.value = corrected;

                if (document.activeElement === input && typeof start === 'number') {
                    const diff = corrected.length - prevLength;
                    const nextPos = Math.max(0, (end ?? start) + diff);
                    input.setSelectionRange(nextPos, nextPos);
                }
            }
        };

        if (live) {
            input.addEventListener('input', apply);
        }

        input.addEventListener('blur', apply);
        input.form?.addEventListener('submit', apply);
        apply();
    });
};

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('app-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const openBtn = document.getElementById('sidebar-open');
    const closeBtn = document.getElementById('sidebar-close');

    const openSidebar = () => {
        sidebar?.classList.remove('-translate-x-full');
        overlay?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const closeSidebar = () => {
        sidebar?.classList.add('-translate-x-full');
        overlay?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    openBtn?.addEventListener('click', openSidebar);
    closeBtn?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    document.querySelectorAll('[data-sidebar-dropdown]').forEach((dropdown) => {
        const toggle = dropdown.querySelector('[data-sidebar-dropdown-toggle]');
        const panel = dropdown.querySelector('[data-sidebar-dropdown-panel]');
        const chevron = dropdown.querySelector('[data-sidebar-dropdown-chevron]');

        if (!toggle || !panel) {
            return;
        }

        const setOpen = (open) => {
            panel.classList.toggle('hidden', !open);
            chevron?.classList.toggle('rotate-180', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            dropdown.dataset.open = open ? '1' : '0';
        };

        toggle.addEventListener('click', () => {
            setOpen(dropdown.dataset.open !== '1');
        });
    });

    const confirmModal = document.getElementById('confirm-modal');
    const confirmMessage = document.getElementById('confirm-modal-message');
    const confirmYes = document.getElementById('confirm-modal-yes');
    const confirmNo = document.getElementById('confirm-modal-no');
    const confirmBackdrop = document.getElementById('confirm-modal-backdrop');
    const confirmMathWrap = document.getElementById('confirm-math-wrap');
    const confirmMathLabel = document.getElementById('confirm-math-label');
    const confirmMathInput = document.getElementById('confirm-math-input');
    const confirmMathError = document.getElementById('confirm-math-error');

    let pendingConfirmAction = null;
    let expectedMathAnswer = null;

    const openConfirmModal = (message, { requireMath = false } = {}) =>
        new Promise((resolve) => {
            if (!confirmModal || !confirmMessage) {
                resolve(window.confirm(message));
                return;
            }

            confirmMessage.textContent = message;
            confirmMathError?.classList.add('hidden');

            if (requireMath && confirmMathWrap && confirmMathLabel && confirmMathInput) {
                const a = Math.floor(Math.random() * 8) + 2;
                const b = Math.floor(Math.random() * 8) + 2;
                expectedMathAnswer = a + b;
                confirmMathLabel.textContent = `Solve: ${a} + ${b} = ?`;
                confirmMathInput.value = '';
                confirmMathWrap.classList.remove('hidden');
            } else {
                expectedMathAnswer = null;
                confirmMathWrap?.classList.add('hidden');
            }

            confirmModal.classList.remove('hidden');
            confirmModal.classList.add('flex');
            confirmModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');

            if (requireMath) {
                confirmMathInput?.focus();
            } else {
                confirmYes?.focus();
            }

            pendingConfirmAction = resolve;
        });

    const closeConfirmModal = (result) => {
        if (!confirmModal) {
            return;
        }

        confirmModal.classList.add('hidden');
        confirmModal.classList.remove('flex');
        confirmModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        confirmMathWrap?.classList.add('hidden');
        expectedMathAnswer = null;

        if (typeof pendingConfirmAction === 'function') {
            pendingConfirmAction(result);
            pendingConfirmAction = null;
        }
    };

    confirmYes?.addEventListener('click', () => {
        if (expectedMathAnswer !== null) {
            const given = Number(confirmMathInput?.value);
            if (given !== expectedMathAnswer) {
                confirmMathError?.classList.remove('hidden');
                confirmMathInput?.focus();
                return;
            }
        }

        closeConfirmModal(true);
    });

    confirmNo?.addEventListener('click', () => closeConfirmModal(false));
    confirmBackdrop?.addEventListener('click', () => closeConfirmModal(false));

    confirmMathInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            confirmYes?.click();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && confirmModal && !confirmModal.classList.contains('hidden')) {
            closeConfirmModal(false);
        }
    });

    document.querySelectorAll('[data-confirm], [data-math-confirm]').forEach((el) => {
        const handler = async (event) => {
            event.preventDefault();
            event.stopImmediatePropagation();

            const requireMath = el.hasAttribute('data-math-confirm');
            const message =
                el.getAttribute(requireMath ? 'data-math-confirm' : 'data-confirm') ||
                'Are you sure?';

            const confirmed = await openConfirmModal(message, { requireMath });

            if (!confirmed) {
                return;
            }

            if (el.tagName === 'FORM') {
                el.submit();
                return;
            }

            if (el.tagName === 'A' && el.href) {
                window.location.href = el.href;
            }
        };

        if (el.tagName === 'FORM') {
            el.addEventListener('submit', handler);
        } else {
            el.addEventListener('click', handler);
        }
    });

    bindAutoCorrect('[data-format="sl-nic"]', normalizeNic);
    bindAutoCorrect('[data-format="sl-phone"]', normalizePhone);

    const waitingCard = document.getElementById('waiting-approval-card');
    if (waitingCard) {
        const statusUrl = waitingCard.dataset.statusUrl;
        const pollMs = Number(waitingCard.dataset.pollMs || 8000);
        const liveLabel = document.getElementById('waiting-live-label');
        const refreshBtn = document.getElementById('waiting-refresh');

        const checkStatus = async () => {
            if (!statusUrl) {
                return;
            }

            try {
                if (liveLabel) {
                    liveLabel.textContent = 'Checking status…';
                }

                const response = await fetch(statusUrl, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Unable to check status');
                }

                const data = await response.json();

                if (data.can_login && data.redirect) {
                    if (liveLabel) {
                        liveLabel.textContent = 'Approved! Opening your profile…';
                    }
                    window.location.href = data.redirect;
                    return;
                }

                if (liveLabel) {
                    const time = new Date().toLocaleTimeString();
                    liveLabel.textContent = `Still waiting · last checked ${time}`;
                }
            } catch (error) {
                if (liveLabel) {
                    liveLabel.textContent = 'Could not refresh status. Try again.';
                }
            }
        };

        refreshBtn?.addEventListener('click', checkStatus);
        window.setInterval(checkStatus, pollMs);
    }

    document.querySelectorAll('[data-member-bulk-form]').forEach((bulkForm) => {
        const formId = bulkForm.id;
        const selectAll = bulkForm.querySelector('.bulk-select-all')
            || document.querySelector(`.bulk-select-all[form="${formId}"]`);
        const checkboxes = () => Array.from(
            document.querySelectorAll(`.member-bulk-checkbox[form="${formId}"]`),
        );
        const actionSelect = bulkForm.querySelector('.bulk-action');
        const applyBtn = bulkForm.querySelector('.bulk-apply');
        const countLabel = bulkForm.querySelector('.bulk-selected-count');

        const syncBulkUi = () => {
            const selected = checkboxes().filter((box) => box.checked);
            const total = checkboxes().length;
            if (countLabel) {
                countLabel.textContent = String(selected.length);
            }
            if (selectAll) {
                selectAll.checked = total > 0 && selected.length === total;
                selectAll.indeterminate = selected.length > 0 && selected.length < total;
            }
            const enabled = selected.length > 0;
            if (actionSelect) {
                actionSelect.disabled = !enabled;
            }
            if (applyBtn) {
                applyBtn.disabled = !enabled || !actionSelect?.value;
            }
        };

        selectAll?.addEventListener('change', () => {
            checkboxes().forEach((box) => {
                box.checked = selectAll.checked;
            });
            syncBulkUi();
        });

        document.addEventListener('change', (event) => {
            if (
                (event.target?.classList?.contains('member-bulk-checkbox') && event.target.getAttribute('form') === formId)
                || event.target === actionSelect
            ) {
                syncBulkUi();
            }
        });

        bulkForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const selected = checkboxes().filter((box) => box.checked);
            const action = actionSelect?.value;

            if (!selected.length || !action) {
                return;
            }

            const labels = {
                approve: 'Approve',
                reject: 'Reject',
                reaccept: 'Re-accept',
                activate: 'Activate',
                deactivate: 'Deactivate',
                require_password_change: 'Require password change for',
                reset_password: 'Reset password for',
                delete: 'Delete',
            };

            const requireMath = action === 'delete';
            const message = `${labels[action] || 'Update'} ${selected.length} selected member(s)?`;
            const confirmed = await openConfirmModal(message, { requireMath });

            if (!confirmed) {
                return;
            }

            bulkForm.submit();
        });

        syncBulkUi();
    });

    document.querySelectorAll('[data-auto-filter]').forEach((form) => {
        let searchTimer = null;

        form.querySelectorAll('[data-auto-filter-change]').forEach((el) => {
            el.addEventListener('change', () => form.requestSubmit());
        });

        form.querySelectorAll('[data-auto-filter-search]').forEach((el) => {
            el.addEventListener('input', () => {
                window.clearTimeout(searchTimer);
                searchTimer = window.setTimeout(() => form.requestSubmit(), 400);
            });
        });
    });

    document.querySelectorAll('[data-member-tabs]').forEach((root) => {
        const buttons = Array.from(root.querySelectorAll('[data-tab-btn]'));
        const panels = Array.from(root.querySelectorAll('[data-tab-panel]'));
        const defaultTab = root.dataset.defaultTab || buttons[0]?.dataset.tabBtn;

        const activate = (tab) => {
            buttons.forEach((btn) => {
                const active = btn.dataset.tabBtn === tab;
                btn.classList.toggle('is-active', active);
                btn.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            panels.forEach((panel) => {
                panel.classList.toggle('hidden', panel.dataset.tabPanel !== tab);
            });
        };

        buttons.forEach((btn) => {
            btn.addEventListener('click', () => activate(btn.dataset.tabBtn));
        });

        activate(defaultTab);
    });

    const venuesList = document.getElementById('venues-list');
    const addVenueBtn = document.getElementById('add-venue-btn');
    const venueTemplate = document.getElementById('venue-block-template');

    if (venuesList && addVenueBtn && venueTemplate) {
        const venueBlocks = () => Array.from(venuesList.querySelectorAll('.venue-block'));

        const reindexVenues = () => {
            venueBlocks().forEach((block, index) => {
                block.dataset.venueIndex = String(index);
                const number = block.querySelector('.venue-number');
                if (number) {
                    number.textContent = String(index + 1);
                }

                block.querySelectorAll('[name^="venues["]').forEach((input) => {
                    const name = input.getAttribute('name') || '';
                    input.setAttribute('name', name.replace(/venues\[\d+]/, `venues[${index}]`));
                });

                const removeBtn = block.querySelector('.remove-venue-btn');
                if (removeBtn) {
                    removeBtn.classList.toggle('hidden', venueBlocks().length <= 1);
                }
            });
        };

        addVenueBtn.addEventListener('click', () => {
            const index = venueBlocks().length;
            const html = venueTemplate.innerHTML
                .replaceAll('__INDEX__', String(index))
                .replaceAll('__NUMBER__', String(index + 1));

            venuesList.insertAdjacentHTML('beforeend', html);
            reindexVenues();
        });

        venuesList.addEventListener('click', (event) => {
            const button = event.target.closest('.remove-venue-btn');
            if (!button) {
                return;
            }

            const block = button.closest('.venue-block');
            if (!block || venueBlocks().length <= 1) {
                return;
            }

            block.remove();
            reindexVenues();
        });

        reindexVenues();
    }

    const daysList = document.getElementById('days-list');
    const addDayBtn = document.getElementById('add-day-btn');
    const dayTemplate = document.getElementById('day-block-template');
    const sessionTemplate = document.getElementById('session-block-template');
    const questionTemplate = document.getElementById('question-block-template');
    const optionTemplate = document.getElementById('option-block-template');

    if (daysList && addDayBtn && dayTemplate) {
        const maxDays = Number(daysList.dataset.maxDays || 14);

        const dayBlocks = () => Array.from(daysList.querySelectorAll('.day-block'));

        const reindexOptions = (questionBlock, dayIndex, questionIndex) => {
            const options = Array.from(questionBlock.querySelectorAll('.option-block'));
            options.forEach((option, optionIndex) => {
                option.dataset.optionIndex = String(optionIndex);
                option.querySelectorAll('[name*="[options]"]').forEach((input) => {
                    const name = input.getAttribute('name') || '';
                    input.setAttribute(
                        'name',
                        name.replace(
                            /days\[\d+\]\[questions\]\[\d+\]\[options\]\[\d+\]/,
                            `days[${dayIndex}][questions][${questionIndex}][options][${optionIndex}]`
                        )
                    );
                });
                const removeBtn = option.querySelector('.remove-option-btn');
                if (removeBtn) {
                    removeBtn.classList.toggle('hidden', options.length <= 2);
                }
            });
        };

        const reindexQuestions = (dayBlock, dayIndex) => {
            const questions = Array.from(dayBlock.querySelectorAll('.question-block'));
            questions.forEach((question, questionIndex) => {
                question.dataset.questionIndex = String(questionIndex);
                const number = question.querySelector('.question-number');
                if (number) {
                    number.textContent = String(questionIndex + 1);
                }

                question.querySelectorAll('[name*="[questions]"]').forEach((input) => {
                    const name = input.getAttribute('name') || '';
                    input.setAttribute(
                        'name',
                        name.replace(/days\[\d+\]\[questions\]\[\d+\]/, `days[${dayIndex}][questions][${questionIndex}]`)
                    );
                });

                reindexOptions(question, dayIndex, questionIndex);
            });
        };

        const reindexSessions = (dayBlock, dayIndex) => {
            const sessions = Array.from(dayBlock.querySelectorAll('.session-block'));
            sessions.forEach((session, sessionIndex) => {
                session.dataset.sessionIndex = String(sessionIndex);
                const number = session.querySelector('.session-number');
                if (number) {
                    number.textContent = String(sessionIndex + 1);
                }

                session.querySelectorAll('[name*="[sessions]"]').forEach((input) => {
                    const name = input.getAttribute('name') || '';
                    input.setAttribute(
                        'name',
                        name.replace(/days\[\d+\]\[sessions\]\[\d+\]/, `days[${dayIndex}][sessions][${sessionIndex}]`)
                    );
                });

                const removeBtn = session.querySelector('.remove-session-btn');
                if (removeBtn) {
                    removeBtn.classList.toggle('hidden', sessions.length <= 1);
                }
            });
        };

        const reindexDays = () => {
            dayBlocks().forEach((block, index) => {
                block.dataset.dayIndex = String(index);
                const number = block.querySelector('.day-block-number');
                if (number) {
                    number.textContent = String(index + 1);
                }

                block.querySelectorAll('[name^="days["]').forEach((input) => {
                    const name = input.getAttribute('name') || '';
                    input.setAttribute('name', name.replace(/days\[\d+]/, `days[${index}]`));
                });

                const dayNumberInput = block.querySelector('.day-number-input');
                if (dayNumberInput && !dayNumberInput.value) {
                    dayNumberInput.value = String(index + 1);
                }

                const removeBtn = block.querySelector('.remove-day-btn');
                if (removeBtn) {
                    removeBtn.classList.toggle('hidden', dayBlocks().length <= 1);
                }

                reindexSessions(block, index);
                reindexQuestions(block, index);
            });

            addDayBtn.disabled = dayBlocks().length >= maxDays;
            addDayBtn.classList.toggle('opacity-50', addDayBtn.disabled);
        };

        addDayBtn.addEventListener('click', () => {
            if (dayBlocks().length >= maxDays) {
                return;
            }

            const index = dayBlocks().length;
            const html = dayTemplate.innerHTML
                .replaceAll('__INDEX__', String(index))
                .replaceAll('__NUMBER__', String(index + 1));

            daysList.insertAdjacentHTML('beforeend', html);
            reindexDays();
        });

        daysList.addEventListener('click', (event) => {
            const addSessionBtn = event.target.closest('.add-session-btn');
            if (addSessionBtn && sessionTemplate) {
                const dayBlock = addSessionBtn.closest('.day-block');
                const sessionsList = dayBlock?.querySelector('.sessions-list');
                if (!dayBlock || !sessionsList) {
                    return;
                }

                const dayIndex = Number(dayBlock.dataset.dayIndex || 0);
                const sessionIndex = sessionsList.querySelectorAll('.session-block').length;
                const html = sessionTemplate.innerHTML
                    .replaceAll('__DAY_INDEX__', String(dayIndex))
                    .replaceAll('__SESSION_INDEX__', String(sessionIndex))
                    .replaceAll('__SESSION_NUMBER__', String(sessionIndex + 1));

                sessionsList.insertAdjacentHTML('beforeend', html);
                reindexSessions(dayBlock, dayIndex);
                return;
            }

            const removeSessionBtn = event.target.closest('.remove-session-btn');
            if (removeSessionBtn) {
                const dayBlock = removeSessionBtn.closest('.day-block');
                const session = removeSessionBtn.closest('.session-block');
                const sessions = dayBlock ? Array.from(dayBlock.querySelectorAll('.session-block')) : [];
                if (!dayBlock || !session || sessions.length <= 1) {
                    return;
                }

                session.remove();
                reindexSessions(dayBlock, Number(dayBlock.dataset.dayIndex || 0));
                return;
            }

            const addQuestionBtn = event.target.closest('.add-question-btn');
            if (addQuestionBtn && questionTemplate) {
                const dayBlock = addQuestionBtn.closest('.day-block');
                const questionsList = dayBlock?.querySelector('.questions-list');
                if (!dayBlock || !questionsList) {
                    return;
                }

                const dayIndex = Number(dayBlock.dataset.dayIndex || 0);
                const questionIndex = questionsList.querySelectorAll('.question-block').length;
                const html = questionTemplate.innerHTML
                    .replaceAll('__DAY_INDEX__', String(dayIndex))
                    .replaceAll('__QUESTION_INDEX__', String(questionIndex))
                    .replaceAll('__QUESTION_NUMBER__', String(questionIndex + 1));

                questionsList.insertAdjacentHTML('beforeend', html);
                reindexQuestions(dayBlock, dayIndex);
                return;
            }

            const removeQuestionBtn = event.target.closest('.remove-question-btn');
            if (removeQuestionBtn) {
                const dayBlock = removeQuestionBtn.closest('.day-block');
                const question = removeQuestionBtn.closest('.question-block');
                if (!dayBlock || !question) {
                    return;
                }

                question.remove();
                reindexQuestions(dayBlock, Number(dayBlock.dataset.dayIndex || 0));
                return;
            }

            const addOptionBtn = event.target.closest('.add-option-btn');
            if (addOptionBtn && optionTemplate) {
                const dayBlock = addOptionBtn.closest('.day-block');
                const questionBlock = addOptionBtn.closest('.question-block');
                const optionsList = questionBlock?.querySelector('.options-list');
                if (!dayBlock || !questionBlock || !optionsList) {
                    return;
                }

                const dayIndex = Number(dayBlock.dataset.dayIndex || 0);
                const questionIndex = Number(questionBlock.dataset.questionIndex || 0);
                const optionIndex = optionsList.querySelectorAll('.option-block').length;
                const html = optionTemplate.innerHTML
                    .replaceAll('__DAY_INDEX__', String(dayIndex))
                    .replaceAll('__QUESTION_INDEX__', String(questionIndex))
                    .replaceAll('__OPTION_INDEX__', String(optionIndex));

                optionsList.insertAdjacentHTML('beforeend', html);
                reindexOptions(questionBlock, dayIndex, questionIndex);
                return;
            }

            const removeOptionBtn = event.target.closest('.remove-option-btn');
            if (removeOptionBtn) {
                const dayBlock = removeOptionBtn.closest('.day-block');
                const questionBlock = removeOptionBtn.closest('.question-block');
                const option = removeOptionBtn.closest('.option-block');
                const options = questionBlock ? Array.from(questionBlock.querySelectorAll('.option-block')) : [];
                if (!dayBlock || !questionBlock || !option || options.length <= 2) {
                    return;
                }

                option.remove();
                reindexOptions(
                    questionBlock,
                    Number(dayBlock.dataset.dayIndex || 0),
                    Number(questionBlock.dataset.questionIndex || 0)
                );
                return;
            }

            const removeDayBtn = event.target.closest('.remove-day-btn');
            if (!removeDayBtn) {
                return;
            }

            const block = removeDayBtn.closest('.day-block');
            if (!block || dayBlocks().length <= 1) {
                return;
            }

            block.remove();
            reindexDays();
        });

        reindexDays();
    }

    document.querySelectorAll('[data-org-cascade]').forEach((root) => {
        const treeEl = root.querySelector('[data-org-tree]');
        const instituteSelect = root.querySelector('[data-org-institute]');
        const subSelect = root.querySelector('[data-org-sub-institute]');
        const sectionSelect = root.querySelector('[data-org-section]');

        if (!treeEl || !instituteSelect || !subSelect || !sectionSelect) {
            return;
        }

        let tree = [];
        try {
            tree = JSON.parse(treeEl.textContent || '[]');
        } catch (e) {
            tree = [];
        }

        const fillSelect = (select, items, placeholder, selected) => {
            const current = selected ?? select.value;
            select.innerHTML = '';
            const empty = document.createElement('option');
            empty.value = '';
            empty.textContent = placeholder;
            select.appendChild(empty);

            items.forEach((item) => {
                const option = document.createElement('option');
                option.value = item;
                option.textContent = item;
                if (item === current) {
                    option.selected = true;
                }
                select.appendChild(option);
            });

            if (current && !items.includes(current)) {
                const legacy = document.createElement('option');
                legacy.value = current;
                legacy.textContent = `${current} (legacy)`;
                legacy.selected = true;
                select.appendChild(legacy);
            }
        };

        const refreshSubs = (preserveSection = false) => {
            const institute = tree.find((item) => item.name === instituteSelect.value);
            const subs = institute ? institute.sub_institutes.map((s) => s.name) : [];
            const selectedSub = subSelect.dataset.selected || subSelect.value;
            fillSelect(subSelect, subs, 'Select sub-institute', selectedSub);
            subSelect.dataset.selected = '';
            refreshSections(preserveSection);
        };

        const refreshSections = () => {
            const institute = tree.find((item) => item.name === instituteSelect.value);
            const sub = institute?.sub_institutes?.find((item) => item.name === subSelect.value);
            const sections = sub ? sub.sections : [];
            const selectedSection = sectionSelect.dataset.selected || sectionSelect.value;
            fillSelect(sectionSelect, sections, 'Select section', selectedSection);
            sectionSelect.dataset.selected = '';
        };

        instituteSelect.addEventListener('change', () => {
            subSelect.dataset.selected = '';
            sectionSelect.dataset.selected = '';
            refreshSubs();
        });

        subSelect.addEventListener('change', () => {
            sectionSelect.dataset.selected = '';
            refreshSections();
        });

        refreshSubs(true);
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const roleSelect = document.querySelector('[data-user-role-select]');
    const receptionEvents = document.querySelector('[data-reception-events]');
    if (roleSelect && receptionEvents) {
        const syncReceptionVisibility = () => {
            receptionEvents.classList.toggle('hidden', roleSelect.value !== 'reception');
        };
        roleSelect.addEventListener('change', syncReceptionVisibility);
        syncReceptionVisibility();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const desk = document.getElementById('attendance-desk');
    if (!desk) {
        return;
    }

    const lookupUrl = desk.dataset.lookupUrl;
    const checkinUrl = desk.dataset.checkinUrl;
    const updateItemsUrl = desk.dataset.updateItemsUrl;
    const csrf = desk.dataset.csrf;
    const venueRequired = desk.dataset.venueRequired === '1';
    let dayId = desk.dataset.dayId;
    let currentMemberId = null;
    let currentAttendanceId = null;
    let deskMode = 'check_in';
    let lookupLock = false;
    let lastScanned = '';
    let lastScanAt = 0;

    const scanStatus = desk.querySelector('[data-scan-status]');
    const manualCode = desk.querySelector('[data-manual-code]');
    const manualLookupBtn = desk.querySelector('[data-manual-lookup]');
    const matchList = desk.querySelector('[data-match-list]');
    const banner = desk.querySelector('[data-result-banner]');
    const emptyState = desk.querySelector('[data-member-empty]');
    const memberCard = desk.querySelector('[data-member-card]');
    const checkinBtn = desk.querySelector('[data-checkin-btn]');
    const checkinVenue = desk.querySelector('[data-checkin-venue]');
    const checkedList = desk.querySelector('[data-checked-list]');
    const checkedCards = desk.querySelector('[data-checked-cards]');
    const checkedCount = desk.querySelector('[data-checked-count]');
    const daySelect = document.getElementById('day');
    const deskVenueSelect = desk.querySelector('[data-venue-select]');

    const syncDeskVenueToCheckin = () => {
        if (!checkinVenue || !deskVenueSelect) {
            return;
        }
        const selectedVenueId = String(deskVenueSelect.value || '');
        if (!selectedVenueId) {
            return;
        }
        const optionExists = Array.from(checkinVenue.options).some((option) => option.value === selectedVenueId);
        if (optionExists) {
            checkinVenue.value = selectedVenueId;
        }

        desk.querySelectorAll('input[type="hidden"][name="venue"]').forEach((input) => {
            input.value = selectedVenueId;
        });
    };

    const setBanner = (type, message) => {
        if (!banner) {
            return;
        }
        banner.classList.remove('hidden', 'bg-brand-green-soft', 'text-brand-green', 'border', 'border-brand-green/20');
        banner.classList.remove('bg-brand-orange-soft', 'text-brand-orange', 'border-brand-orange/20');
        banner.classList.remove('bg-red-50', 'text-red-700', 'border-red-200');
        banner.classList.add('border');
        if (type === 'success') {
            banner.classList.add('bg-brand-green-soft', 'text-brand-green', 'border-brand-green/20');
        } else if (type === 'warn') {
            banner.classList.add('bg-brand-orange-soft', 'text-brand-orange', 'border-brand-orange/20');
        } else {
            banner.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
        }
        banner.textContent = message;
    };

    const hideMatches = () => {
        if (!matchList) {
            return;
        }
        matchList.classList.add('hidden');
        matchList.innerHTML = '';
    };

    const clearPreview = () => {
        currentMemberId = null;
        currentAttendanceId = null;
        deskMode = 'check_in';
        emptyState?.classList.remove('hidden');
        memberCard?.classList.add('hidden');
        setItemChecks([]);
        if (checkinBtn) {
            checkinBtn.disabled = true;
            checkinBtn.textContent = checkinBtn.dataset.labelCheckin || 'Check in';
        }
    };

    const setItemChecks = (itemIds = []) => {
        const selected = new Set((itemIds || []).map((id) => String(id)));
        desk.querySelectorAll('[data-checkin-item]').forEach((input) => {
            input.checked = selected.has(String(input.value));
        });
    };

    const selectedItemIds = () =>
        Array.from(desk.querySelectorAll('[data-checkin-item]:checked'))
            .map((input) => Number(input.value))
            .filter((id) => Number.isFinite(id) && id > 0);

    const resetForNextMember = () => {
        clearPreview();
        hideMatches();
        if (manualCode) {
            manualCode.value = '';
            manualCode.focus();
        }
        lastScanned = '';
        lastScanAt = 0;
    };

    const showMember = (payload) => {
        hideMatches();
        emptyState?.classList.add('hidden');
        memberCard?.classList.remove('hidden');

        const member = payload.member || {};
        currentMemberId = member.id || null;
        currentAttendanceId = payload.attendance?.id || null;
        deskMode = payload.can_update_items ? 'update_items' : 'check_in';

        desk.querySelector('[data-member-name]').textContent = member.name || '—';
        desk.querySelector('[data-member-unique]').textContent = member.unique_id || '—';
        desk.querySelector('[data-member-nic]').textContent = member.nic || '—';
        desk.querySelector('[data-member-mode]').textContent = payload.enrollment?.participation_mode || '—';
        desk.querySelector('[data-member-institute]').textContent = member.institute || '—';
        desk.querySelector('[data-member-meta]').textContent = [member.designation, member.category, member.mobile]
            .filter(Boolean)
            .join(' · ') || '—';

        const photoWrap = desk.querySelector('[data-member-photo-wrap]');
        const photo = desk.querySelector('[data-member-photo]');
        if (member.photo_url && photo && photoWrap) {
            photo.src = member.photo_url;
            photoWrap.classList.remove('hidden');
        } else if (photoWrap) {
            photoWrap.classList.add('hidden');
        }

        syncDeskVenueToCheckin();
        setItemChecks(payload.attendance?.item_ids || []);

        if (checkinBtn) {
            const canAct = Boolean(payload.can_check_in || payload.can_update_items);
            checkinBtn.disabled = !canAct;
            checkinBtn.textContent = payload.can_update_items
                ? checkinBtn.dataset.labelUpdate || 'Update items given'
                : checkinBtn.dataset.labelCheckin || 'Check in';
        }
    };

    const showMatches = (matches) => {
        if (!matchList) {
            return;
        }
        clearPreview();
        matchList.classList.remove('hidden');
        matchList.innerHTML = matches
            .map(
                (member) => `
            <button type="button" class="flex w-full items-start justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-left hover:border-brand-green hover:bg-brand-green-soft" data-pick-member="${member.id}">
                <span>
                    <span class="block text-sm font-semibold text-ink">${member.name || '—'}</span>
                    <span class="mt-0.5 block text-xs text-muted">${[member.unique_id, member.nic, member.mobile].filter(Boolean).join(' · ') || '—'}</span>
                </span>
                <span class="shrink-0 text-xs font-semibold text-brand-blue">Select</span>
            </button>`
            )
            .join('');
    };

    const escapeHtml = (value) =>
        String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');

    const itemsBadgesHtml = (items) => {
        if (!Array.isArray(items) || items.length === 0) {
            return '—';
        }

        return `<div class="flex max-w-xs flex-wrap gap-1">${items
            .map((name) => `<span class="badge-muted">${escapeHtml(name)}</span>`)
            .join('')}</div>`;
    };

    const itemsText = (items) => {
        if (!Array.isArray(items) || items.length === 0) {
            return '—';
        }

        return items.map((name) => String(name)).join(', ');
    };

    const prependCheckedIn = (row) => {
        const onFirstPage = String(desk.dataset.listPage || '1') === '1';
        const hasListSearch = String(desk.dataset.listSearch || '').trim() !== '';

        if (checkedCount) {
            checkedCount.textContent = String(Number(checkedCount.textContent || '0') + 1);
        }

        if (!onFirstPage || hasListSearch) {
            return;
        }

        if (checkedList) {
            checkedList.querySelector('[data-empty-row]')?.remove();
            const tr = document.createElement('tr');
            if (row.id) {
                tr.setAttribute('data-attendance-id', String(row.id));
            }
            tr.innerHTML = `
                <td class="font-semibold text-ink">${escapeHtml(row.member_name || '—')}</td>
                <td class="text-muted">${escapeHtml(row.unique_id || '—')}</td>
                <td class="text-muted">${escapeHtml(row.venue || '—')}</td>
                <td class="text-muted" data-attendance-items>${itemsBadgesHtml(row.items)}</td>
                <td class="text-muted whitespace-nowrap">Just now</td>
                <td><span class="font-semibold text-ink">${escapeHtml(row.officer || '—')}</span></td>
                <td>${row.profile_url ? `<a href="${escapeHtml(row.profile_url)}" class="btn-outline">View</a>` : ''}</td>
            `;
            checkedList.prepend(tr);
        }

        if (checkedCards) {
            checkedCards.querySelector('[data-empty-cards]')?.remove();
            const card = document.createElement('article');
            card.className = 'rounded-xl border border-slate-200 bg-surface/40 p-3.5';
            if (row.id) {
                card.setAttribute('data-attendance-id', String(row.id));
            }
            card.innerHTML = `
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-ink">${escapeHtml(row.member_name || '—')}</p>
                        <p class="mt-0.5 break-all text-xs font-semibold text-brand-blue">${escapeHtml(row.unique_id || '—')}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-xs text-muted">Just now</p>
                        ${row.profile_url ? `<a href="${escapeHtml(row.profile_url)}" class="mt-2 inline-flex text-xs font-semibold text-brand-blue underline">View profile</a>` : ''}
                    </div>
                </div>
                <dl class="mt-3 grid gap-2 text-sm">
                    <div class="flex gap-2">
                        <dt class="w-20 shrink-0 text-xs font-semibold uppercase tracking-wide text-muted">Venue</dt>
                        <dd class="min-w-0 break-words text-ink">${escapeHtml(row.venue || '—')}</dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="w-20 shrink-0 text-xs font-semibold uppercase tracking-wide text-muted">Items</dt>
                        <dd class="min-w-0 break-words text-ink" data-attendance-items>${escapeHtml(itemsText(row.items))}</dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="w-20 shrink-0 text-xs font-semibold uppercase tracking-wide text-muted">Officer</dt>
                        <dd class="min-w-0 font-semibold text-ink">${escapeHtml(row.officer || '—')}</dd>
                    </div>
                </dl>
            `;
            checkedCards.prepend(card);
        }
    };

    const updateCheckedInItems = (row) => {
        if (!row?.id) {
            return;
        }

        const id = String(row.id);
        checkedList?.querySelectorAll(`[data-attendance-id="${id}"]`).forEach((tr) => {
            const cell = tr.querySelector('[data-attendance-items]');
            if (cell) {
                cell.innerHTML = itemsBadgesHtml(row.items);
            }
        });

        checkedCards?.querySelectorAll(`[data-attendance-id="${id}"]`).forEach((card) => {
            const cell = card.querySelector('[data-attendance-items]');
            if (cell) {
                cell.textContent = itemsText(row.items);
            }
        });
    };

    const applyLookupPayload = (payload) => {
        if (payload.status === 'multiple' && Array.isArray(payload.matches)) {
            showMatches(payload.matches);
            setBanner('warn', payload.message);
            return;
        }

        if (payload.member) {
            showMember(payload);
        } else {
            clearPreview();
            hideMatches();
        }

        if (payload.status === 'already_checked_in') {
            setBanner('warn', payload.message);
        } else if (payload.ok) {
            setBanner('success', payload.message);
        } else {
            setBanner('error', payload.message || 'Lookup failed.');
        }
    };

    const lookup = async (payloadBody) => {
        if (lookupLock) {
            return;
        }

        lookupLock = true;
        try {
            const response = await fetch(lookupUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({
                    event_day_id: Number(dayId),
                    ...payloadBody,
                }),
            });
            const payload = await response.json();
            applyLookupPayload(payload);
        } catch (error) {
            setBanner('error', 'Could not look up this member. Try again.');
            clearPreview();
            hideMatches();
        } finally {
            lookupLock = false;
        }
    };

    const searchManual = () => {
        const q = String(manualCode?.value || '').trim();
        if (!q) {
            setBanner('error', 'Enter a unique ID, NIC, name, or mobile number.');
            return;
        }
        lookup({ q });
    };

    manualLookupBtn?.addEventListener('click', searchManual);
    manualCode?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            searchManual();
        }
    });

    matchList?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-pick-member]');
        if (!button) {
            return;
        }
        lookup({ member_id: Number(button.getAttribute('data-pick-member')) });
    });

    checkinBtn?.addEventListener('click', async () => {
        if (!currentMemberId || checkinBtn.disabled) {
            return;
        }

        const venueId = checkinVenue ? Number(checkinVenue.value) : null;
        if (deskMode === 'check_in' && venueRequired && !venueId) {
            setBanner('error', 'Select a venue before check-in.');
            return;
        }

        if (deskMode === 'update_items' && !currentAttendanceId) {
            setBanner('error', 'No check-in record found to update.');
            return;
        }

        const itemIds = selectedItemIds();
        checkinBtn.disabled = true;

        try {
            const isUpdate = deskMode === 'update_items';
            const response = await fetch(isUpdate ? updateItemsUrl : checkinUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify(
                    isUpdate
                        ? {
                              attendance_id: currentAttendanceId,
                              item_ids: itemIds,
                          }
                        : {
                              member_id: currentMemberId,
                              event_day_id: Number(dayId),
                              event_venue_id: venueId,
                              item_ids: itemIds,
                          }
                ),
            });
            const payload = await response.json();

            if (payload.ok) {
                if (isUpdate && payload.attendance) {
                    updateCheckedInItems(payload.attendance);
                } else if (!isUpdate && payload.attendance) {
                    prependCheckedIn(payload.attendance);
                }
                resetForNextMember();
                setBanner(
                    'success',
                    (payload.message || (isUpdate ? 'Items updated.' : 'Checked in.')) + ' Ready for the next member.'
                );
            } else {
                setBanner(payload.status === 'already_checked_in' ? 'warn' : 'error', payload.message || 'Action failed.');
                checkinBtn.disabled = false;
            }
        } catch (error) {
            setBanner('error', deskMode === 'update_items' ? 'Could not update items. Try again.' : 'Check-in failed. Try again.');
            checkinBtn.disabled = false;
        }
    });

    daySelect?.addEventListener('change', () => {
        dayId = daySelect.value;
    });

    deskVenueSelect?.addEventListener('change', () => {
        syncDeskVenueToCheckin();
    });

    desk.querySelector('[data-items-select-all]')?.addEventListener('click', () => {
        const boxes = Array.from(desk.querySelectorAll('[data-checkin-item]'));
        const allChecked = boxes.length > 0 && boxes.every((box) => box.checked);
        boxes.forEach((box) => {
            box.checked = !allChecked;
        });
    });

    syncDeskVenueToCheckin();

    import('html5-qrcode')
        .then(({ Html5Qrcode }) => {
            const readerId = 'qr-reader';
            const scanner = new Html5Qrcode(readerId);
            const boxSize = Math.max(180, Math.min(250, Math.floor(window.innerWidth * 0.55)));

            scanner
                .start(
                    { facingMode: 'environment' },
                    { fps: 8, qrbox: { width: boxSize, height: boxSize }, aspectRatio: 1 },
                    (decodedText) => {
                        const now = Date.now();
                        const code = String(decodedText || '').trim().toUpperCase();
                        if (!code) {
                            return;
                        }
                        if (code === lastScanned && now - lastScanAt < 2500) {
                            return;
                        }
                        lastScanned = code;
                        lastScanAt = now;
                        if (manualCode) {
                            manualCode.value = code;
                        }
                        lookup({ code });
                    },
                    () => {}
                )
                .then(() => {
                    if (scanStatus) {
                        scanStatus.textContent = 'Camera ready — scan a member QR code.';
                    }
                })
                .catch(() => {
                    if (scanStatus) {
                        scanStatus.textContent = 'Camera unavailable. Use search by ID, name, or mobile.';
                    }
                });
        })
        .catch(() => {
            if (scanStatus) {
                scanStatus.textContent = 'Scanner library failed to load. Use search by ID, name, or mobile.';
            }
        });
});

document.addEventListener('DOMContentLoaded', () => {
    const lockScreen = document.getElementById('desk-lock-screen');
    if (lockScreen) {
        const unlockUrl = lockScreen.dataset.unlockUrl;
        const csrf = lockScreen.dataset.csrf;
        const messageEl = lockScreen.querySelector('[data-pin-message]');
        const dots = Array.from(lockScreen.querySelectorAll('[data-dot]'));
        let pin = '';
        let busy = false;

        const renderDots = () => {
            dots.forEach((dot, index) => {
                dot.classList.toggle('bg-brand-green', index < pin.length);
                dot.classList.toggle('bg-slate-600', index >= pin.length);
            });
        };

        const setMessage = (text, isError = false) => {
            if (!messageEl) {
                return;
            }
            messageEl.textContent = text || '';
            messageEl.classList.toggle('text-red-400', isError);
            messageEl.classList.toggle('text-slate-300', !isError);
        };

        const submitPin = async () => {
            if (busy || pin.length !== 4) {
                return;
            }
            busy = true;
            setMessage('Unlocking…');
            try {
                const response = await fetch(unlockUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ pin }),
                });
                const payload = await response.json();
                if (payload.ok) {
                    setMessage('Unlocked');
                    window.location.href = payload.redirect || '/admin/attendance';
                    return;
                }
                setMessage(payload.message || 'Incorrect PIN.', true);
                pin = '';
                renderDots();
            } catch (error) {
                setMessage('Could not unlock. Try again.', true);
                pin = '';
                renderDots();
            } finally {
                busy = false;
            }
        };

        const pushDigit = (digit) => {
            if (busy || pin.length >= 4) {
                return;
            }
            pin += digit;
            renderDots();
            if (pin.length === 4) {
                submitPin();
            }
        };

        lockScreen.querySelectorAll('[data-pin-key]').forEach((button) => {
            button.addEventListener('click', () => {
                const key = button.getAttribute('data-pin-key');
                if (key === 'clear') {
                    pin = '';
                    setMessage('');
                    renderDots();
                    return;
                }
                if (key === 'del') {
                    pin = pin.slice(0, -1);
                    setMessage('');
                    renderDots();
                    return;
                }
                pushDigit(key);
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key >= '0' && event.key <= '9') {
                event.preventDefault();
                pushDigit(event.key);
            } else if (event.key === 'Backspace') {
                event.preventDefault();
                pin = pin.slice(0, -1);
                setMessage('');
                renderDots();
            } else if (event.key === 'Escape') {
                pin = '';
                setMessage('');
                renderDots();
            }
        });

        renderDots();
        return;
    }

    document.addEventListener('keydown', (event) => {
        if (!(event.ctrlKey || event.metaKey) || event.key.toLowerCase() !== 'l') {
            return;
        }
        const lockForm = document.querySelector('form[action*="attendance/lock"]');
        if (!lockForm) {
            return;
        }
        event.preventDefault();
        lockForm.requestSubmit();
    });
});

const reportPalette = ['#0b5d3b', '#1e3a5f', '#c2410c', '#0f766e', '#475569', '#b45309', '#1d4ed8', '#047857'];

const parseChartJson = (value) => {
    try {
        return JSON.parse(value || '[]');
    } catch {
        return [];
    }
};

document.querySelectorAll('[data-report-chart]').forEach((canvas) => {
    const type = canvas.getAttribute('data-chart-type') || 'bar';
    const labels = parseChartJson(canvas.getAttribute('data-chart-labels'));
    const values = parseChartJson(canvas.getAttribute('data-chart-values')).map((v) => Number(v) || 0);
    const colors = labels.map((_, index) => reportPalette[index % reportPalette.length]);
    const isDoughnut = type === 'doughnut' || type === 'pie';

    // eslint-disable-next-line no-new
    new Chart(canvas, {
        type,
        data: {
            labels,
            datasets: [
                {
                    label: 'Count',
                    data: values,
                    backgroundColor: isDoughnut || type === 'bar' ? colors : 'rgba(11, 93, 59, 0.18)',
                    borderColor: isDoughnut ? '#ffffff' : '#0b5d3b',
                    borderWidth: isDoughnut ? 2 : 2,
                    borderRadius: type === 'bar' ? 6 : 0,
                    tension: 0.35,
                    fill: type === 'line',
                    pointBackgroundColor: '#0b5d3b',
                    pointRadius: type === 'line' ? 3 : 0,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: isDoughnut,
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        usePointStyle: true,
                        padding: 16,
                    },
                },
                tooltip: {
                    backgroundColor: '#132744',
                    titleFont: { weight: '600' },
                    padding: 10,
                },
            },
            scales: isDoughnut
                ? {}
                : {
                      x: {
                          grid: { display: false },
                          ticks: {
                              maxRotation: 45,
                              minRotation: 0,
                              autoSkip: true,
                              maxTicksLimit: 12,
                          },
                      },
                      y: {
                          beginAtZero: true,
                          ticks: { precision: 0 },
                          grid: { color: 'rgba(148, 163, 184, 0.25)' },
                      },
                  },
        },
    });
});

(() => {
    const modal = document.getElementById('profile-crop-modal');
    const cropImage = document.getElementById('profile-crop-image');
    if (!modal || !cropImage) {
        return;
    }

    const backdrop = modal.querySelector('[data-profile-crop-backdrop]');
    const cancelBtn = modal.querySelector('[data-profile-crop-cancel]');
    const applyBtn = modal.querySelector('[data-profile-crop-apply]');

    let cropper = null;
    let activeInput = null;
    let objectUrl = null;

    const closeModal = () => {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
        cropImage.removeAttribute('src');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
        activeInput = null;
    };

    const openModal = (input, file) => {
        activeInput = input;
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
        }
        objectUrl = URL.createObjectURL(file);
        cropImage.src = objectUrl;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');

        if (cropper) {
            cropper.destroy();
        }

        cropper = new Cropper(cropImage, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            responsive: true,
            background: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: false,
            toggleDragModeOnDblclick: false,
            ready() {
                // Keep square locked — disable free resize handles via cropBoxResizable:false
            },
        });
    };

    const applyCrop = () => {
        if (!cropper || !activeInput) {
            return;
        }

        const canvas = cropper.getCroppedCanvas({
            width: 800,
            height: 800,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        if (!canvas) {
            closeModal();
            return;
        }

        canvas.toBlob(
            (blob) => {
                if (!blob || !activeInput) {
                    closeModal();
                    return;
                }

                const fileName = 'profile.jpg';
                const croppedFile = new File([blob], fileName, { type: 'image/jpeg', lastModified: Date.now() });
                const transfer = new DataTransfer();
                transfer.items.add(croppedFile);
                activeInput.files = transfer.files;

                const root = activeInput.closest('[data-profile-image-crop]');
                const previewWrap = root?.querySelector('[data-profile-image-preview-wrap]');
                const preview = root?.querySelector('[data-profile-image-preview]');
                const current = root?.querySelector('[data-profile-image-current]');

                if (preview && previewWrap) {
                    preview.src = URL.createObjectURL(blob);
                    previewWrap.classList.remove('hidden');
                }
                current?.classList.add('hidden');

                closeModal();
            },
            'image/jpeg',
            0.92,
        );
    };

    document.querySelectorAll('[data-profile-image-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) {
                return;
            }
            if (!file.type.startsWith('image/')) {
                input.value = '';
                return;
            }
            openModal(input, file);
            // Clear native selection until crop is applied
            input.value = '';
        });
    });

    cancelBtn?.addEventListener('click', () => {
        if (activeInput) {
            activeInput.value = '';
        }
        closeModal();
    });

    backdrop?.addEventListener('click', () => {
        if (activeInput) {
            activeInput.value = '';
        }
        closeModal();
    });

    applyBtn?.addEventListener('click', applyCrop);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            if (activeInput) {
                activeInput.value = '';
            }
            closeModal();
        }
    });
})();
