document.addEventListener('DOMContentLoaded', function() {
    const gradePoints = { 'A': 4.0, 'AB': 3.5, 'B': 3.0, 'BC': 2.5, 'C': 2.0, 'D': 1.0, 'E': 0.0 };
    // The initial course list is empty.
    const initialCourses = [];
    const minEnglishScoresForGraduation = { s1: 450, s2: 475, s3: 500 };

    const coursesList = document.getElementById('courses-list');
    const programLevelSelect = document.getElementById('program-level');
    const englishScoreInput = document.getElementById('english-score');
    const englishResultDiv = document.getElementById('english-score-result');
    const modal = document.getElementById('ai-feedback-modal');
    const modalLoader = document.getElementById('modal-loader');
    const modalResponse = document.getElementById('modal-response');

    window.openModal = function() { modal.style.display = 'flex'; modalLoader.style.display = 'flex'; modalResponse.style.display = 'none'; modalResponse.innerHTML = ''; }
    window.closeModal = function() { modal.style.display = 'none'; }
    
    window.getAIFeedback = async function() {
        openModal();
        const studentName = document.getElementById('student-name').value;
        const studentNIM = document.getElementById('student-nim').value;
        const gpa = document.getElementById('result').textContent;
        const coursesData = [];
        coursesList.querySelectorAll('.course-card').forEach(row => {
            const name = row.querySelector('input[type="text"]').value;
            const sks = row.querySelector('input[type="number"]').value;
            const grade = row.querySelector('select').value;
            if(name && sks && grade) { // Only send complete data
                 coursesData.push(`- ${name} (${sks} SKS): Nilai ${grade}`);
            }
        });

        if (coursesData.length === 0) { 
            modalResponse.innerHTML = '<p>Silakan isi setidaknya satu mata kuliah lengkap dengan SKS dan Nilai untuk mendapatkan umpan balik.</p>';
            modalLoader.style.display = 'none';
            modalResponse.style.display = 'block'; 
            return; 
        }
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch('/api/ai-feedback', { 
                method: 'POST', 
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }, 
                body: JSON.stringify({
                    studentName,
                    studentNIM,
                    gpa,
                    courses: coursesData,
                }) 
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.error || 'Terjadi kesalahan pada server.');
            
            if (result.feedback) {
                modalResponse.innerHTML = result.feedback;
            } else { throw new Error("Respon dari AI tidak valid."); }
        } catch (error) {
            console.error("AI Feedback Error:", error);
            modalResponse.innerHTML = `<p class="text-telkom-bright-red">Maaf, terjadi kesalahan: ${error.message}</p>`;
        } finally {
            modalLoader.style.display = 'none';
            modalResponse.style.display = 'block';
        }
    }

    window.checkEnglishScore = function() {
        const program = programLevelSelect.value;
        const score = parseInt(englishScoreInput.value, 10);
        englishResultDiv.textContent = '';
        if (isNaN(score)) return;
        const minScore = minEnglishScoresForGraduation[program];
        if (score >= minScore) {
            englishResultDiv.textContent = `Selamat! Skor Anda memenuhi syarat kelulusan (min. ${minScore}).`;
            englishResultDiv.className = 'text-xs mt-2 h-4 text-green-600 font-semibold';
        } else {
            englishResultDiv.textContent = `Maaf, skor Anda belum memenuhi syarat kelulusan (min. ${minScore}).`;
            englishResultDiv.className = 'text-xs mt-2 h-4 text-red-600 font-semibold';
        }
    }

    function getPredicate(gpa, level) { if (level === 's1') { if (gpa >= 3.91) return 'Sempurna'; if (gpa >= 3.51) return 'Dengan Pujian / Sangat Memuaskan'; if (gpa >= 2.76) return 'Memuaskan'; } if (level === 's2') { if (gpa >= 3.96) return 'Sempurna'; if (gpa >= 3.76) return 'Dengan Pujian / Sangat Memuaskan'; if (gpa >= 3.26) return 'Memuaskan'; } if (level === 's3') { if (gpa >= 3.96) return 'Sempurna'; if (gpa >= 3.76) return 'Dengan Pujian / Sangat Memuaskan'; if (gpa >= 3.26) return 'Memuaskan'; } return '-'; }
    function updatePredicateTableVisibility() { const selectedLevel = programLevelSelect.value; document.querySelectorAll('.predicate-table').forEach(table => table.style.display = 'none'); document.getElementById(`predicate-${selectedLevel}`).style.display = 'block'; }
    function calculateGPA() { let totalPoints = 0, totalSKS = 0; coursesList.querySelectorAll('.course-card').forEach(row => { const sks = parseInt(row.querySelector('input[type="number"]').value, 10); const grade = row.querySelector('select').value; if (!isNaN(sks) && grade && gradePoints[grade] !== undefined) { totalPoints += sks * gradePoints[grade]; totalSKS += sks; } }); const gpa = totalSKS > 0 ? (totalPoints / totalSKS) : 0; const selectedLevel = programLevelSelect.value; document.getElementById('result').textContent = gpa.toFixed(2); document.getElementById('predicate-result').textContent = getPredicate(gpa, selectedLevel); document.getElementById('summary').textContent = `Berdasarkan total ${totalSKS} SKS yang telah dinilai.`; }
    function createCourseRow(course) { const row = document.createElement('div'); row.className = 'course-card bg-white p-4 rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200 flex flex-col gap-3'; row.innerHTML = `<div class="flex justify-between items-start"><input type="text" placeholder="Nama Mata Kuliah" class="w-full text-base font-semibold p-1 -ml-1 text-black bg-transparent focus:bg-gray-100 rounded" value="${course.name}"><button onclick="removeCourse(this)" class="text-gray-400 hover:text-red-600 p-1 rounded-full ml-2 flex-shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button></div><div class="grid grid-cols-2 gap-3"><div><label class="text-xs font-medium text-gray-500">SKS</label><input type="number" placeholder="SKS" class="mt-1 w-full p-2.5 bg-gray-50 border-gray-300 text-sm rounded-lg" min="1" value="${course.sks}"></div><div><label class="text-xs font-medium text-gray-500">Nilai</label><select class="mt-1 w-full p-2.5 bg-gray-50 border-gray-300 text-sm rounded-lg"><option value="">Pilih</option><option value="A" ${course.grade==='A'?'selected':''}>A</option><option value="AB" ${course.grade==='AB'?'selected':''}>AB</option><option value="B" ${course.grade==='B'?'selected':''}>B</option><option value="BC" ${course.grade==='BC'?'selected':''}>BC</option><option value="C" ${course.grade==='C'?'selected':''}>C</option><option value="D" ${course.grade==='D'?'selected':''}>D</option><option value="E" ${course.grade==='E'?'selected':''}>E</option></select></div></div>`; return row; }
    window.addCourse = function() { coursesList.appendChild(createCourseRow({ name: '', sks: '', grade: '' })); }
    window.removeCourse = function(button) { button.closest('.course-card').remove(); calculateGPA(); }
    
    if (initialCourses.length > 0) {
        initialCourses.forEach(course => coursesList.appendChild(createCourseRow(course)));
    } else {
        // Start with one empty row if the list is empty
        addCourse();
    }
    
    coursesList.addEventListener('input', calculateGPA);
    programLevelSelect.addEventListener('change', () => {
        updatePredicateTableVisibility();
        calculateGPA();
        checkEnglishScore();
    });
    englishScoreInput.addEventListener('input', checkEnglishScore);
    updatePredicateTableVisibility();
    calculateGPA();
});
