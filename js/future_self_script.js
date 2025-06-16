// JavaScript moved from futureself.php to this file
// All logic for navigation, validation, review, and dynamic UI

// --- State for answers ---
let answers = {};
// --- Save all answers in a flat structure for each section ---
function saveCurrentSection(idx) {
    const fs = document.getElementById('category-' + idx);
    if (!fs) return;
    answers[idx] = {};
    // Save all select fields
    fs.querySelectorAll('select').forEach(function(select) {
        let label = select.closest('label') ? select.closest('label').innerText.trim() : getPreviousLabelText(select);
        if (label) {
            answers[idx][label] = select.value;
            // If 'Other' is selected, save the corresponding text
            if (select.value === 'Other') {
                const otherInput = select.parentElement.querySelector('input[type="text"]');
                if (otherInput && otherInput.value) {
                    answers[idx][label + ' - Other'] = otherInput.value;
                }
            }
        }
    });
    // Save all text inputs (for 'Other' and free text)
    fs.querySelectorAll('input[type="text"]').forEach(function(input) {
        let label = getPreviousLabelText(input);
        if (label && input.value) {
            answers[idx][label] = input.value;
        }
    });
    // Save all checkbox groups (fix for multi-answer questions)
    fs.querySelectorAll('.checkbox-group').forEach(function(group) {
        const label = group.previousElementSibling ? group.previousElementSibling.innerText.trim() : '';
        if (label) {
            const checked = Array.from(group.querySelectorAll('input[type="checkbox"]')).filter(cb => cb.checked).map(cb => cb.value);
            answers[idx][label] = checked; // Always save as array
            // If 'Other' is checked, save the corresponding text
            const otherCb = group.querySelector('input[type="checkbox"][value="Other"]');
            if (otherCb && otherCb.checked) {
                const otherInput = group.querySelector('input[type="text"]');
                if (otherInput && otherInput.value) {
                    answers[idx][label + ' - Other'] = otherInput.value;
                }
            }
        }
    });
    // Save all radio fields (for stage of life)
    if (Number(idx) === 0) {
        const selected = document.querySelector('#category-0 input[type="radio"][name="stage"]:checked');
        if (selected) {
            answers[0]['Stage of Life'] = selected.parentElement.innerText.trim();
        }
    }
}
// --- Navigation logic ---
const totalCategories = 7;
let currentCategory = 0;
function showCategory(idx) {
    for (let i = 0; i < totalCategories; i++) {
        document.getElementById('category-' + i).style.display = (i === idx) ? 'block' : 'none';
    }
    document.getElementById('review-section').style.display = (idx === totalCategories) ? 'block' : 'none';
    document.getElementById('prev-category').style.display = idx > 0 && idx < totalCategories ? 'inline-block' : 'none';
    document.getElementById('next-category').style.display = (idx < totalCategories - 1) ? 'inline-block' : 'none';
    document.getElementById('review-btn').style.display = (idx === totalCategories - 1) ? 'inline-block' : 'none';
}
document.getElementById('prev-category').onclick = function() {
    saveCurrentSection(currentCategory);
    if (currentCategory > 0) {
        currentCategory--;
        showCategory(currentCategory);
    }
};
document.getElementById('next-category').onclick = function(e) {
    if (e) e.preventDefault();
    // Validate and highlight before moving to next category
    if (!validateCategory(currentCategory, true)) return;
    saveCurrentSection(currentCategory);
    if (currentCategory < totalCategories - 1) {
        currentCategory++;
        showCategory(currentCategory);
    }
};
document.getElementById('review-btn').onclick = function() {
    if (!validateCategory(currentCategory, true)) return;
    saveCurrentSection(currentCategory);
    prepareReview();
    currentCategory++;
    showCategory(currentCategory);
};
document.getElementById('edit-answers').onclick = function() {
    currentCategory = 0;
    showCategory(currentCategory);
};
// On page load
showCategory(currentCategory);
// --- Validation and highlighting ---
function validateCategory(idx, highlight = false) {
    let valid = true;
    const fs = document.getElementById('category-' + idx);
    fs.querySelectorAll('.missing-answer').forEach(el => el.classList.remove('missing-answer'));
    // Validate selects and text inputs
    fs.querySelectorAll('select, input[type="text"]').forEach(function(input) {
        if (input.hasAttribute('required') && !input.value) {
            if (highlight) input.classList.add('missing-answer');
            valid = false;
        } else {
            input.classList.remove('missing-answer');
        }
    });
    // Validate checkboxes (pick N, robust for multi-answer)
    fs.querySelectorAll('.checkbox-group').forEach(function(group) {
        let min = 0;
        if (group.hasAttribute('data-min')) {
            min = parseInt(group.getAttribute('data-min'), 10) || 0;
        }
        const checkboxes = group.querySelectorAll('input[type="checkbox"]');
        const checked = Array.from(checkboxes).filter(cb => cb.checked);
        if (min > 0 && checked.length < min) {
            if (highlight) group.classList.add('missing-answer');
            valid = false;
        } else {
            group.classList.remove('missing-answer');
        }
        // If 'Other' is checked, require the text
        const otherCb = group.querySelector('input[type="checkbox"][value="Other"]');
        if (otherCb && otherCb.checked) {
            const otherInput = group.querySelector('input[type="text"]');
            if (!otherInput.value) {
                if (highlight) otherInput.classList.add('missing-answer');
                valid = false;
            } else {
                otherInput.classList.remove('missing-answer');
            }
        }
    });
    // Special case for Stage of Life (category-0)
    if (Number(idx) === 0) {
        const radios = document.querySelectorAll('#category-0 input[type="radio"][name="stage"]');
        const checked = Array.from(radios).some(r => r.checked);
        if (!checked) {
            if (highlight) document.getElementById('category-0').classList.add('missing-answer');
            valid = false;
        } else {
            document.getElementById('category-0').classList.remove('missing-answer');
        }
    }
    if (!valid && highlight) {
        alert('Please answer all required questions in this section before continuing.');
        const first = fs.querySelector('.missing-answer');
        if (first) first.scrollIntoView({behavior: 'smooth'});
    }
    return valid;
}
// --- Review logic ---
function prepareReview() {
    const reviewDiv = document.getElementById('review-content');
    reviewDiv.innerHTML = '';
    let allAnswered = true;
    const totalCategories = 7;
    for (let idx = 0; idx < totalCategories; idx++) {
        const fs = document.getElementById('category-' + idx);
        let html = `<h4>${fs.querySelector('legend') ? fs.querySelector('legend').innerText : ''}</h4><ul>`;
        if (idx === 0) {
            let found = false;
            const radios = fs.querySelectorAll('input[type="radio"][name="stage"]');
            radios.forEach(radio => {
                if (radio.checked) {
                    html += `<li><span class="review-answer">${radio.parentElement.innerText.trim()}</span></li>`;
                    found = true;
                }
            });
            if (!found) {
                html += `<li style="color:red;font-weight:bold;">Stage of Life: <span style="color:#555;"><em>Not answered</em></span></li>`;
                allAnswered = false;
            }
            html += '</ul>';
            reviewDiv.innerHTML += html;
            continue;
        }
        // Order-preserving review logic
        let children = Array.from(fs.childNodes);
        for (let i = 0; i < children.length; i++) {
            const node = children[i];
            if (node.nodeType !== 1) continue; // skip text nodes
            // Multi-answer (checkbox-group)
            if (node.classList && node.classList.contains('checkbox-group')) {
                // Find the label before this group
                let label = node.previousElementSibling;
                while (label && (label.nodeType !== 1 || label.tagName !== 'LABEL')) label = label.previousElementSibling;
                if (!label) continue;
                const qKey = label.innerText.trim();
                const checked = Array.from(node.querySelectorAll('input[type="checkbox"]:checked')).map(cb => cb.value);
                let displayArr = checked.slice();
                if (checked.includes('Other')) {
                    const otherInput = node.querySelector('input[type="text"]');
                    if (otherInput && otherInput.value) {
                        displayArr = displayArr.map(v => v === 'Other' ? otherInput.value : v);
                    }
                }
                const displayValue = displayArr.length > 0 ? displayArr.join(', ') : '';
                if (displayValue) {
                    html += `<li>${qKey}: <span class="review-answer">${displayValue}</span></li>`;
                } else {
                    html += `<li style="color:red;font-weight:bold;">${qKey}: <span style="color:#555;"><em>Not answered</em></span></li>`;
                    allAnswered = false;
                }
                continue;
            }
            // Single-answer (select or text input not in group)
            if (node.tagName === 'LABEL') {
                // Check next element sibling for select or input[type=text]
                let next = node.nextElementSibling;
                while (next && (next.tagName === 'BR' || next.nodeType !== 1)) next = next.nextElementSibling;
                if (!next) continue;
                if (next.classList && next.classList.contains('checkbox-group')) continue; // handled above
                if (next.tagName === 'SELECT') {
                    let displayValue = '';
                    if (next.value && next.value !== 'Other') {
                        displayValue = next.value;
                    } else if (next.value === 'Other') {
                        // Find the next input[type=text] sibling for 'Other'
                        let otherInput = next.parentElement.querySelector('input[type="text"]');
                        if (otherInput && otherInput.value) displayValue = otherInput.value;
                        // FIX: If 'Other' is selected but text is empty, treat as unanswered
                        if (!displayValue) {
                            html += `<li style="color:red;font-weight:bold;">${node.innerText.trim()}: <span style="color:#555;"><em>Not answered</em></span></li>`;
                            allAnswered = false;
                            continue;
                        }
                    }
                    if (displayValue) {
                        html += `<li>${node.innerText.trim()}: <span class="review-answer">${displayValue}</span></li>`;
                    } else {
                        html += `<li style="color:red;font-weight:bold;">${node.innerText.trim()}: <span style="color:#555;"><em>Not answered</em></span></li>`;
                        allAnswered = false;
                    }
                    continue;
                }
                if (next.tagName === 'INPUT' && next.type === 'text') {
                    // Only show if not an 'Other' for a select
                    let prev = node.previousElementSibling;
                    let skip = false;
                    while (prev) {
                        if (prev.tagName === 'SELECT' && prev.value === 'Other') { skip = true; break; }
                        prev = prev.previousElementSibling;
                    }
                    if (skip) continue;
                    let displayValue = next.value;
                    if (displayValue) {
                        html += `<li>${node.innerText.trim()}: <span class="review-answer">${displayValue}</span></li>`;
                    } else {
                        html += `<li style="color:red;font-weight:bold;">${node.innerText.trim()}: <span style="color:#555;"><em>Not answered</em></span></li>`;
                        allAnswered = false;
                    }
                    continue;
                }
            }
        }
        html += '</ul>';
        reviewDiv.innerHTML += html;
    }
    if (!allAnswered) {
        reviewDiv.innerHTML += '<div style="color:red;font-weight:bold;">Please answer all required questions before submitting.</div>';
        document.querySelector('.submit-btn').disabled = true;
    } else {
        document.querySelector('.submit-btn').disabled = false;
    }
}
// Prevent submit if not all required answered
if (document.getElementById('futureself-form')) {
    document.getElementById('futureself-form').onsubmit = function(e) {
        prepareReview();
        if (document.querySelector('.submit-btn').disabled) {
            e.preventDefault();
            alert('Please answer all required questions before submitting.');
            return false;
        }
    };
}
// --- Live update answers on every change ---
document.addEventListener('input', function(e) {
    // Find which section this input/select/checkbox belongs to
    let fs = e.target.closest('fieldset.category-section');
    if (!fs) return;
    let idx = parseInt(fs.id.replace('category-', ''));
    saveCurrentSection(idx);
});
// Helper: Get previous label text
function getPreviousLabelText(el) {
    // Try to find the closest previous label, even skipping <br> and text nodes
    let prev = el.previousSibling;
    while (prev) {
        if (prev.nodeType === 1 && prev.tagName === 'LABEL') return prev.innerText.trim();
        prev = prev.previousSibling;
    }
    // If not found, try to find the label by traversing up the parent chain
    let parent = el.parentElement;
    while (parent) {
        let labels = parent.querySelectorAll('label');
        for (let i = labels.length - 1; i >= 0; i--) {
            if (labels[i].compareDocumentPosition(el) & Node.DOCUMENT_POSITION_FOLLOWING) {
                return labels[i].innerText.trim();
            }
        }
        parent = parent.parentElement;
    }
    // Fallback: try to extract from name attribute
    if (el.name) {
        return el.name.replace(/^responses\[[^\]]+\]\[|\]$/g, '').replace(/_/g, ' ');
    }
    return '';
}
// Show/hide 'Other' text inputs for select fields
function handleOther(selectClass, inputClass) {
    document.querySelectorAll('select.' + selectClass).forEach(function(select) {
        select.addEventListener('change', function() {
            var input = select.parentElement.querySelector('input.' + inputClass);
            if (select.value === 'Other') {
                input.style.display = 'inline-block';
                input.required = true;
            } else {
                input.style.display = 'none';
                input.required = false;
            }
        });
    });
}
function handleOtherMulti(selectName, inputClass) {
    document.querySelectorAll('select[name^="' + selectName + '"]').forEach(function(select) {
        select.addEventListener('change', function() {
            var input = select.parentElement.querySelector('input.' + inputClass);
            var found = false;
            for (var i = 0; i < select.options.length; i++) {
                if (select.options[i].selected && select.options[i].value === 'Other') found = true;
            }
            if (found) {
                input.style.display = 'inline-block';
                input.required = true;
            } else {
                input.style.display = 'none';
                input.required = false;
            }
        });
    });
}
document.addEventListener('DOMContentLoaded', function() {
    handleOther('hair-colour-select', 'hair-colour-other');
    handleOther('posture-select', 'posture-other');
    handleOther('dress-select', 'dress-other');
    handleOther('income-earn-select', 'income-earn-other');
    handleOther('asset-select', 'asset-other');
    handleOther('saved-select', 'saved-other');
    handleOther('hobbies-select', 'hobbies-other');
    handleOther('sacrifice-select', 'sacrifice-other');
    handleOther('reframe-select', 'reframe-other');
    handleOther('friends-select', 'friends-other');
    handleOther('friends-do-select', 'friends-do-other');
    handleOther('boundaries-select', 'boundaries-other');
    handleOtherMulti('responses[Emotional/Spiritual Values][2. What are your core values (pick three)?]', 'core-values-other');
    handleOtherMulti('responses[Emotional/Spiritual Values][3. How will you keep yourself emotionally balanced and healthy (pick three)?]', 'balanced-other');
    handleOtherMulti('responses[Emotional/Spiritual Values][4. How do your core values relate to your financial goal (pick two)?]', 'core-values-relate-other');
    // For core values (pick three)
    limitCheckboxes('fieldset#category-3 .checkbox-group:nth-of-type(1)', 3);
    // For balanced and healthy (pick three)
    limitCheckboxes('fieldset#category-3 .checkbox-group:nth-of-type(2)', 3);
    // For core values relate (pick two)
    limitCheckboxes('fieldset#category-3 .checkbox-group:nth-of-type(3)', 2);
    // Add redirect for Next button in previous responses section
    var prevNextBtn = document.getElementById('next-category');
    if (prevNextBtn && document.querySelector('.future-self-results')) {
        prevNextBtn.addEventListener('click', function() {
            window.location.href = '../generate_avatar/avatar_frontpage.php';
        });
    }
    // For all .checkbox-group with data-min, apply limitCheckboxes
    document.querySelectorAll('.checkbox-group[data-min]').forEach(function(group) {
        const min = parseInt(group.getAttribute('data-min'), 10) || 0;
        if (min > 0) limitCheckboxes('#' + group.id + ', .' + group.className.split(' ').join('.'), min);
    });
});
// Show/hide 'Other' text inputs for Emotional/Spiritual Values checkboxes
function handleCheckboxOther(checkboxClass, inputClass) {
    document.querySelectorAll('.' + checkboxClass).forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            var input = checkbox.parentElement.parentElement.querySelector('input.' + inputClass);
            input.style.display = checkbox.checked ? 'inline-block' : 'none';
            input.required = checkbox.checked;
        });
    });
}
handleCheckboxOther('core-values-other-checkbox', 'core-values-other');
handleCheckboxOther('balanced-other-checkbox', 'balanced-other');
handleCheckboxOther('core-values-relate-other-checkbox', 'core-values-relate-other');
// Checkbox limit logic
function limitCheckboxes(selector, max) {
    document.querySelectorAll(selector).forEach(function(group) {
        group.addEventListener('change', function() {
            const checkboxes = group.querySelectorAll('input[type="checkbox"]');
            const checked = Array.from(checkboxes).filter(cb => cb.checked);
            if (max > 0 && checked.length >= max) {
                checkboxes.forEach(cb => {
                    if (!cb.checked) cb.disabled = true;
                });
            } else {
                checkboxes.forEach(cb => cb.disabled = false);
            }
        });
    });
}
// After successful form submission, show success message and redirect to review page
if (document.getElementById('futureself-form')) {
    document.getElementById('futureself-form').addEventListener('submit', function(e) {
        if (!document.querySelector('.submit-btn').disabled) {
            document.getElementById('success-message').style.display = 'block';
        }
    });
}
