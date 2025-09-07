<!doctype html>
<html>
<head>
   <link rel="stylesheet" href="/css/quiz.css">
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
  <div class="container" id="quiz-root">
  </div>

  <template id="question-template">
    <div class="question-block">
      <p class="q-text"></p>
      <div class="answers"></div>
      <div>
        <button id="skip-btn">Skip</button>
        <button id="next-btn">Next</button>
      </div>
    </div>
  </template>

<script>
(function () {
  const root = document.getElementById('quiz-root');
  const tmpl = document.getElementById('question-template');
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  let order = 1;
  const MAX_QUESTIONS = 5; // required

  function fetchJSON(url, opts = {}) {
    const defaultHeaders = {'Accept': 'application/json', 'X-CSRF-TOKEN': csrf};
    opts.headers = Object.assign(defaultHeaders, opts.headers || {});
    return fetch(url, opts).then(r => {
      if (!r.ok) return r.json().then(j => Promise.reject(j));
      return r.json();
    });
  }

  function loadQuestion(order) {
    fetchJSON(`/api/question/${order}`)
      .then(renderQuestion)
      .catch(err => {
        root.innerHTML = '<p>No more questions or error.</p>';
      });
  }

  function renderQuestion(q) {
    root.innerHTML = '';
    const clone = tmpl.content.cloneNode(true);
    clone.querySelector('.q-text').textContent = q.question_text;
    const answersDiv = clone.querySelector('.answers');

    q.answers.forEach(a => {
      const label = document.createElement('label');
      label.style.display = 'block';
      const input = document.createElement('input');
      input.type = 'radio';
      input.name = `answer_${q.id}`;
      input.value = a.id;
      // input.addEventListener('change', () => {
        // clone.querySelector('#next-btn').disabled = false;
      // });
      label.appendChild(input);
      label.appendChild(document.createTextNode(' ' + a.answer_text));
      answersDiv.appendChild(label);
    });
    

    const skipBtn = clone.querySelector('#skip-btn');
    const nextBtn = clone.querySelector('#next-btn');

    skipBtn.addEventListener('click', () => submit({question_id: q.id, action: 'skip'}));
    nextBtn.addEventListener('click', () => {
      const selected = root.querySelector(`input[name="answer_${q.id}"]:checked`);
      submit({question_id: q.id, action: 'answer', answer_id: selected.value});
    });

    root.appendChild(clone);
  }

  function submit(payload) {
    fetchJSON('/api/answer', {
      method: 'POST',
      body: JSON.stringify(payload),
      headers: {'Content-Type': 'application/json'}
    })
    .then(() => {
      if (order < MAX_QUESTIONS) {
        order++;
        loadQuestion(order);
      } else {
        // show summary via AJAX
        fetchJSON('/api/summary')
          .then(showSummary)
          .catch(()=> root.innerHTML = '<p>Error fetching summary</p>');
      }
    })
    .catch(err => {
      alert((err && err.error) || 'Submission failed');
    });
  }

  function showSummary(s) {
    root.innerHTML = `<h3>Summary</h3>
      <p>Total: ${s.total}</p>
      <p>Correct: ${s.correct}</p>
      <p>Wrong: ${s.wrong}</p>
      <p>Skipped: ${s.skipped}</p>
      <p>Score: ${s.percentage}%</p>`;
  }

  // initial load
  loadQuestion(order);
})();
</script>
</body>
</html>
