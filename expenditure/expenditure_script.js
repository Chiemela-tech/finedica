// --- Expenditure Review Pattern (like psychometric test) ---
const expenditureCategories = [
    { id: 'salary', name: 'Salary', type: 'income' },
    { id: 'dividends', name: 'Dividends', type: 'income' },
    { id: 'statePension', name: 'State Pension', type: 'income' },
    { id: 'pension', name: 'Pension', type: 'income' },
    { id: 'benefits', name: 'Benefits', type: 'income' },
    { id: 'otherIncome', name: 'Other Income', type: 'income' },
    { id: 'gas', name: 'Gas', type: 'home' },
    { id: 'electric', name: 'Electric', type: 'home' },
    { id: 'water', name: 'Water', type: 'home' },
    { id: 'councilTax', name: 'Council Tax', type: 'home' },
    { id: 'phone', name: 'Phone', type: 'home' },
    { id: 'internet', name: 'Internet', type: 'home' },
    { id: 'mobilePhone', name: 'Mobile Phone', type: 'home' },
    { id: 'food', name: 'Food', type: 'home' },
    { id: 'otherHome', name: 'Other Home', type: 'home' },
    { id: 'petrol', name: 'Petrol', type: 'travel' },
    { id: 'carTax', name: 'Car Tax', type: 'travel' },
    { id: 'carInsurance', name: 'Car Insurance', type: 'travel' },
    { id: 'maintenance', name: 'Maintenance', type: 'travel' },
    { id: 'publicTransport', name: 'Public Transport', type: 'travel' },
    { id: 'otherTravel', name: 'Other Travel', type: 'travel' },
    { id: 'totalIncome', name: 'Total Income', type: 'total' },
    { id: 'totalExpenses', name: 'Total Expenses', type: 'total' },
    { id: 'surplus', name: 'Surplus', type: 'total' }
];

function validateExpenditureForm(form) {
  // Require all fields to be answered
  let valid = true;
  const data = new FormData(form);
  const requiredFields = [
    'salary','dividends','statePension','pension','benefits','otherIncome',
    'gas','electric','water','councilTax','phone','internet','mobilePhone','food','otherHome',
    'petrol','carTax','carInsurance','maintenance','publicTransport','otherTravel',
    'social','holidays','gym','clothing','otherMisc',
    'nursery','childcare','schoolFees','uniCosts','childMaintenance','otherChildren',
    'life','criticalIllness','incomeProtection','buildings','contents','otherInsurance',
    'pensionDed','studentLoan','childcareDed','travelDed','sharesave','otherDeductions'
  ];
  requiredFields.forEach(field => {
    const value = data.get(field);
    const input = form.querySelector(`[name="${field}"]`);
    if (!value || value.trim() === '') {
      valid = false;
      if (input) input.classList.add('highlight-missing');
    } else {
      if (input) input.classList.remove('highlight-missing');
    }
  });
  return valid;
}

document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('reviewBtn').onclick = function() {
      const form = document.getElementById('expenditureForm');
      if (!validateExpenditureForm(form)) {
          alert('Please answer all fields before reviewing.');
          // Optionally scroll to first missing
          const firstMissing = form.querySelector('.highlight-missing');
          if (firstMissing) firstMissing.scrollIntoView({behavior: 'smooth'});
          return;
      }
      const data = new FormData(form);
      // Group fields by category for review
      const categories = [
          { name: 'Income', fields: ['salary','dividends','statePension','pension','benefits','otherIncome'] },
          { name: 'Home Expenses', fields: ['gas','electric','water','councilTax','phone','internet','mobilePhone','food','otherHome'] },
          { name: 'Travel Expenses', fields: ['petrol','carTax','carInsurance','maintenance','publicTransport','otherTravel'] },
          { name: 'Miscellaneous', fields: ['social','holidays','gym','clothing','otherMisc'] },
          { name: 'Children', fields: ['nursery','childcare','schoolFees','uniCosts','childMaintenance','otherChildren'] },
          { name: 'Insurance', fields: ['life','criticalIllness','incomeProtection','buildings','contents','otherInsurance'] },
          { name: 'Pay Slip Deductions', fields: ['pensionDed','studentLoan','childcareDed','travelDed','sharesave','otherDeductions'] }
      ];
      let html = '';
      categories.forEach(cat => {
          html += `<h4 style='color:#2a5d84; margin-top:18px;'>${cat.name}</h4><ul style='list-style:none;padding-left:0;'>`;
          cat.fields.forEach(field => {
              const value = data.get(field);
              html += `<li style='margin-bottom:8px;'><b>${field.replace(/([A-Z])/g, ' $1').replace(/^./, s => s.toUpperCase())}:</b> <span class='review-answer'>${value ? value : '<em>Not answered</em>'}</span></li>`;
          });
          html += '</ul>';
      });
      document.getElementById('reviewList').innerHTML = html;
      form.style.display = 'none';
      document.getElementById('reviewPanel').style.display = '';
  };
  document.getElementById('editBtn').onclick = function() {
      document.getElementById('reviewPanel').style.display = 'none';
      document.getElementById('expenditureForm').style.display = '';
  };
  document.getElementById('submitBtn').onclick = function() {
      const form = document.getElementById('expenditureForm');
      const data = new FormData(form);
      fetch('save_expenditure.php', {
          method: 'POST',
          body: data
      })
      .then(res => res.json())
      .then(data => {
          document.getElementById('reviewPanel').style.display = 'none';
          document.getElementById('resultMsg').innerText = data.success ? "Saved!" : ("Error: " + data.error);
      });
  };
});

// Pie chart for expenditure sections
function getSectionSums(data) {
    const sections = {
        'Home': ['gas','electric','water','councilTax','phone','internet','mobilePhone','food','otherHome'],
        'Travel': ['petrol','carTax','carInsurance','maintenance','publicTransport','otherTravel'],
        'Miscellaneous': ['social','holidays','gym','clothing','otherMisc'],
        'Children': ['nursery','childcare','schoolFees','uniCosts','childMaintenance','otherChildren'],
        'Insurance': ['life','criticalIllness','incomeProtection','buildings','contents','otherInsurance'],
        'Deductions': ['pensionDed','studentLoan','childcareDed','travelDed','sharesave','otherDeductions']
    };
    let sectionSums = {};
    let total = 0;
    for (const [section, fields] of Object.entries(sections)) {
        sectionSums[section] = fields.reduce((sum, f) => sum + (data[f] || 0), 0);
        total += sectionSums[section];
    }
    return {sectionSums, total};
}

function renderExpenditurePieChart(dataSource) {
    let data = {};
    if (typeof dataSource === 'object' && dataSource !== null) {
        data = dataSource;
    } else {
        // Try to get from form if available
        const form = document.getElementById('expenditureForm');
        if (form && form.style.display !== 'none') {
            form.querySelectorAll('input[type="number"]').forEach(input => {
                data[input.name] = parseFloat(input.value) || 0;
            });
        } else {
            // Try to get from summary if available
            const summary = document.getElementById('expenditureSummary');
            if (summary && summary.style.display !== 'none') {
                summary.querySelectorAll('li').forEach(li => {
                    const label = li.querySelector('b').innerText.toLowerCase();
                    const value = parseFloat(li.innerText.split(':')[1]) || 0;
                    data[label] = value;
                });
            }
        }
    }
    const {sectionSums, total} = getSectionSums(data);
    const ctx = document.getElementById('expenseChart').getContext('2d');
    if (window.expenditurePieChart) window.expenditurePieChart.destroy();
    window.expenditurePieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: Object.keys(sectionSums),
            datasets: [{
                data: Object.values(sectionSums),
                backgroundColor: [
                    '#2196f3','#ff9800','#4caf50','#e91e63','#9c27b0','#ffc107'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const percent = total ? ((value/total)*100).toFixed(1) : 0;
                            return `${label}: £${value} (${percent}%)`;
                        }
                    }
                },
                datalabels: {
                    color: '#222',
                    font: { weight: 'bold', size: 14 },
                    formatter: function(value, context) {
                        const percent = total ? ((value/total)*100).toFixed(1) : 0;
                        return percent + '%';
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

// --- Pie chart at review stage ---
function getReviewFormData() {
    const form = document.getElementById('expenditureForm');
    let data = {};
    if (form) {
        form.querySelectorAll('input[type="number"]').forEach(input => {
            data[input.name] = parseFloat(input.value) || 0;
        });
    }
    return data;
}

window.addEventListener('DOMContentLoaded', function() {
    // Initial chart (last submission or form)
    renderExpenditurePieChart();

    // Chart on edit
    const editBtn = document.getElementById('editExpenditureBtn');
    if (editBtn) editBtn.addEventListener('click', function() {
        setTimeout(() => renderExpenditurePieChart(getReviewFormData()), 300);
    });
    // Chart on submit
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) submitBtn.addEventListener('click', function() {
        setTimeout(() => renderExpenditurePieChart(getReviewFormData()), 800);
    });
    // Chart on review
    const reviewBtn = document.getElementById('reviewBtn');
    if (reviewBtn) reviewBtn.addEventListener('click', function() {
        setTimeout(() => renderExpenditurePieChart(getReviewFormData()), 300);
    });
});

