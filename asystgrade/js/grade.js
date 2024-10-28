M.local_asystgrade = {
    init: function(Y, js_data) {
        window.gradeData = js_data;
        document.addEventListener('DOMContentLoaded', function() {
            const apiEndpoint = M.cfg.wwwroot + '/local/asystgrade/api.php';
            const maxmark = document.querySelectorAll("input[name$='-maxmark']")[0].value;
            const answerDivs = document.querySelectorAll(".qtype_essay_response");
            const studentAnswers = Array.from(answerDivs).map(element => element.innerText || element.value);

            const gradesDataRequest = {
                referenceAnswer: document.querySelectorAll(".essay .qtext p")[0].innerHTML,
                studentAnswers: studentAnswers
            };

            fetch(apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(gradesDataRequest)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.grades) {
                        console.log(data.grades);
                        updateMarks(data.grades);
                    } else {
                        console.error('Error in grade response:', data.error);
                    }
                })
                .catch(error => console.error('Error:', error));

            function updateMarks(grades) {
                const inputs = document.querySelectorAll("input[name$='_-mark']");

                grades.forEach((grade, index) => {
                    const predictedGrade = grade.predicted_grade === 'correct' ? maxmark : 0;

                    if (inputs[index]) {
                        inputs[index].value = predictedGrade;
                    } else {
                        console.error(`No grade input found for index: ${index}`);
                    }
                });
            }
        });
    }
};
