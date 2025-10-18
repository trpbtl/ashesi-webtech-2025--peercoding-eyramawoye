const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const openBtn = document.getElementById('open-sidebar');

// Toggle open
openBtn.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
});




document.addEventListener("DOMContentLoaded", () => {
  const container = document.getElementById("cardsContainer");

  fetch("dashboard.json")
    .then(response => {
      if (!response.ok) throw new Error("Network response was not ok");
      return response.json();
    })
    .then(data => {
      data.forEach(course => {
        const card = document.createElement("div");
        card.classList.add("course-card");

        card.innerHTML = `
        
                <div class="card-header">
                    <p><strong>${course.courseId}</strong></p>
                    <h3>${course.courseName}</h3>    
                </div>
                <div class="card-body">
                    <div class="details">
                        <div class="semester-credits">
                            <div><strong>Semester:</strong>
                            <p>${course.semester}</p>
                        </div>
                        <div>
                            <strong>Credits:</strong>
                            <p>${course.credits}</p>
                        </div>
                    </div>   
                </div>
            
                <div class="stats">
                    <p><i class="fas fa-user-graduate"></i> Enrolled <span>${course.enrolled}</span></p>
                    <p><i class="fas fa-user-check"></i> Auditors <span class="green">${course.auditors}</span></p>
                    <p><i class="fas fa-eye"></i> Observers <span class="orange">${course.observers}</span></p>
                    <p><i class="fas fa-chart-line"></i> Attendance <span>${course.avgAttendance}</span></p>
                </div>

                <div class="instructor">
                    <p>Lecturer</p>
                    <strong>${course.instructor}</strong>
                </div>  
              
        `;

        container.appendChild(card);
      });
    })
    .catch(error => {
      console.error("Error loading courses:", error);
      container.innerHTML = "<p>Failed to load course data.</p>";
    });
});