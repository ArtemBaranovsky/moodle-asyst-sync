M.local_asystgrade = {
    init: function(Y, js_data) { // YUI Module entry point
        window.gradeData = js_data;

        document.addEventListener('DOMContentLoaded', function() {
            const apiEndpoint = M.cfg.wwwroot + '/local/asystgrade/api.php';
            const gradesDataRequest = window.gradeData.request;

            if (gradesDataRequest) {
                fetch(apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(gradesDataRequest)
                })
                    .then(response => response.json())
                    .then(data => {
                        updateMarks(data.grades);
                    })
                    .catch(error => console.error('Error:', error));
            }

            function updateMarks(grades) {
                grades.forEach((grade, index) => {
                    console.log(grade, index)
                    const predictedGrade = grade.predicted_grade === 'correct' ? window.gradeData.maxmark : 0;
                    const inputName = window.gradeData.formNames[index];
                    const gradeInput = document.querySelector(`input[name="${inputName}"]`);

                    if (gradeInput) {
                        gradeInput.value = predictedGrade;
                    } else {
                        console.error(`Input field not found for name: ${inputName}`);
                    }
                });
            }
        });
    }
};