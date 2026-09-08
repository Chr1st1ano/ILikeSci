import os

# Create directory
out_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), "lowpai")
os.makedirs(out_dir, exist_ok=True)

css_content = """
body {
    font-family: 'Arial', sans-serif;
    background-color: #e0e0e0;
    color: #000;
    padding: 20px;
    margin: 0;
}
.wireframe-container {
    max-width: 1200px; /* Widescreen */
    margin: 0 auto;
    background: #fff;
    border: 1px solid #000;
    min-height: 675px; /* ~16:9 aspect ratio */
    display: flex;
    flex-direction: column;
}
.header {
    border-bottom: 1px solid #000;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.nav {
    display: flex;
    gap: 20px;
}
.nav-box {
    border: 1px solid #000;
    padding: 8px 16px;
    cursor: pointer;
    background: #fff;
    border-radius: 4px;
}
.content {
    padding: 40px;
    flex: 1;
}
.box {
    border: 1px solid #000;
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 4px;
}
.btn {
    border: 1px solid #000;
    background: #fff;
    padding: 8px 24px;
    display: inline-block;
    cursor: pointer;
    border-radius: 4px;
}
.title {
    font-size: 28px;
    font-weight: normal;
    margin-bottom: 30px;
}
.sidebar-layout {
    display: flex;
    gap: 30px;
}
.sidebar {
    width: 250px;
    border-right: 1px solid #000;
    padding-right: 30px;
}
.main-panel {
    flex: 1;
}
"""

with open(os.path.join(out_dir, "wireframe.css"), "w") as f:
    f.write(css_content)

pages = {
    "login.html": """
        <div class="box" style="min-height: 300px; max-width: 400px; margin: 100px auto; flex-direction: column; gap: 15px;">
            <div class="title">[ ILikeSci Logo ]</div>
            <div>[ Text Input: Teacher Username ]</div>
            <div>[ Text Input: Password ]</div>
            <div class="btn">[ Login to Portal ]</div>
        </div>
    """,
    "dashboard.html": """
        <div class="title">Teacher Dashboard</div>
        <div class="box" style="justify-content: space-between;">
            <div>[ Dropdown: Grade ]</div>
            <div>[ Dropdown: Section ]</div>
            <div>[ Dropdown: Topic ]</div>
        </div>
        <div class="box" style="flex-direction: column; gap: 10px;">
            <div>[ Curriculum Module 1 - Completed ]</div>
            <div>[ Curriculum Module 2 - Current ]</div>
            <div>[ Curriculum Module 3 - Locked ]</div>
        </div>
        <div style="display:flex; gap: 10px;">
            <div class="box" style="flex: 1;">[ Quick Access: Student Records ]</div>
            <div class="box" style="flex: 1;">[ Quick Access: Assessments ]</div>
            <div class="box" style="flex: 1;">[ Quick Access: Materials ]</div>
        </div>
    """,
    "assessment.html": """
        <div class="title">Assessments Management</div>
        <div class="box">[ Filter: Grade / Section / Topic ]</div>
        <div class="box" style="min-height: 200px; flex-direction: column; gap: 20px;">
            <h3>[ Assessment Question Area ]</h3>
            <div style="display: flex; gap: 10px; width: 100%; justify-content: center;">
                <div class="btn" style="flex: 1; text-align: center;">[ Option A ]</div>
                <div class="btn" style="flex: 1; text-align: center;">[ Option B ]</div>
                <div class="btn" style="flex: 1; text-align: center;">[ Option C ]</div>
            </div>
        </div>
        <div style="text-align: right;"><div class="btn">[ Next Question ]</div></div>
    """,
    "scoreboard.html": """
        <div class="title">Student Performance Overview</div>
        <div class="box">[ Filter: Grade / Section ]</div>
        <div class="box" style="flex-direction: column; align-items: stretch;">
            <div style="border-bottom: 1px solid #ccc; padding: 10px; display: flex; justify-content: space-between;">
                <span>#1 [ Avatar ] Student Name</span> <span>[ Total Score ] / [ Participation ]</span>
            </div>
            <div style="border-bottom: 1px solid #ccc; padding: 10px; display: flex; justify-content: space-between;">
                <span>#2 [ Avatar ] Student Name</span> <span>[ Total Score ] / [ Participation ]</span>
            </div>
            <div style="padding: 10px; display: flex; justify-content: space-between;">
                <span>#3 [ Avatar ] Student Name</span> <span>[ Total Score ] / [ Participation ]</span>
            </div>
        </div>
    """,
    "students.html": """
        <div class="title">Student Roster & Sections</div>
        <div class="box" style="justify-content: space-between;">
            <div>[ Filter: Grade ] [ Filter: Section ]</div>
            <div class="btn">[ + Add Student ]</div>
        </div>
        <div class="box" style="flex-direction: column; align-items: flex-start; gap: 10px;">
            <div style="width: 100%; display: flex; justify-content: space-between;"><span>[ Profile ] Name (Grade 4 - Section A)</span> <span class="btn">[ Edit/Delete ]</span></div>
            <div style="width: 100%; display: flex; justify-content: space-between;"><span>[ Profile ] Name (Grade 5 - Section B)</span> <span class="btn">[ Edit/Delete ]</span></div>
        </div>
    """,
    "lessons.html": """
        <div class="title">Interactive Lesson Delivery</div>
        <div class="box" style="min-height: 300px;">
            [ Interactive Slide / Presentation Area (Cast to Class TV) ]
        </div>
        <div style="display: flex; justify-content: space-between;">
            <div class="btn">[ < Previous Slide ]</div>
            <div class="btn">[ Call on Student ]</div>
            <div class="btn">[ Next Slide > ]</div>
        </div>
    """,
    "multimedia.html": """
        <div class="title">Multimedia Library</div>
        <div class="box">[ Filter: Grade / Topic ]</div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="box" style="flex-direction: column;">[ Video Thumbnail ]<br>[ Title ]</div>
            <div class="box" style="flex-direction: column;">[ Video Thumbnail ]<br>[ Title ]</div>
            <div class="box" style="flex-direction: column;">[ Video Thumbnail ]<br>[ Title ]</div>
            <div class="box" style="flex-direction: column;">[ Video Thumbnail ]<br>[ Title ]</div>
        </div>
    """,
    "games.html": """
        <div class="title">Interactive Modules</div>
        <div style="display: flex; gap: 20px;">
            <div class="box" style="flex: 1; flex-direction: column;">[ Module Icon ]<br>Space Explorer Simulation<br><div class="btn">[ Launch ]</div></div>
            <div class="box" style="flex: 1; flex-direction: column;">[ Module Icon ]<br>Cell Builder Activity<br><div class="btn">[ Launch ]</div></div>
        </div>
    """,
    "materials.html": """
        <div class="title">Instructional Materials & Question Bank</div>
        <div class="box" style="justify-content: space-between;">
            <div>[ Select Grade ] [ Select Section ] [ Select Topic ]</div>
            <div><div class="btn">[ Add Topic ]</div> <div class="btn">[ Add Question ]</div></div>
        </div>
        <div class="box" style="flex-direction: column; align-items: stretch;">
            <div style="border-bottom: 1px solid #ccc; padding: 10px;">[ Difficulty Badge ] Question text goes here... [ Edit/Delete ]</div>
            <div style="padding: 10px;">[ Difficulty Badge ] Another question here... [ Edit/Delete ]</div>
        </div>
    """,
    "records.html": """
        <div class="title">Student Records & Reporting</div>
        <div class="box">[ Filter: Grade ] [ Filter: Section ] [ Filter: Topic ] [ Print Report ]</div>
        <div class="box">
            <table style="width: 100%; text-align: left;">
                <tr style="border-bottom: 1px solid #000;"><th>Date</th><th>Student</th><th>Section</th><th>Topic</th><th>Score</th></tr>
                <tr><td>[ Date ]</td><td>[ Name ]</td><td>[ Section A ]</td><td>[ Topic ]</td><td>[ Score/Total ]</td></tr>
                <tr><td>[ Date ]</td><td>[ Name ]</td><td>[ Section B ]</td><td>[ Topic ]</td><td>[ Score/Total ]</td></tr>
            </table>
        </div>
    """,
    "profile.html": """
        <div class="title">Teacher Account Management</div>
        <div class="sidebar-layout">
            <div class="sidebar">
                <div class="box" style="height: 150px; width: 150px; border-radius: 50%;">[ Avatar ]</div>
                <div class="btn" style="width: 100%; margin-bottom: 10px;">[ Change Picture ]</div>
            </div>
            <div class="main-panel">
                <div class="box" style="flex-direction: column; align-items: flex-start; gap: 15px;">
                    <div style="width: 100%;">Teacher Name: <br> [ Text Input ]</div>
                    <div style="width: 100%;">Handled Grades/Sections: <br> [ Text Input ]</div>
                    <div class="btn">[ Save Profile ]</div>
                </div>
            </div>
        </div>
    """,
    "admin.html": """
        <div class="title">System Administration</div>
        <div class="box" style="flex-direction: column; align-items: flex-start; gap: 10px;">
            <h3>Data Synchronization</h3>
            <div style="display: flex; gap: 10px;">
                <div class="btn">[ Sync Offline Records to Cloud ]</div>
                <div class="btn">[ Fetch Updates from Cloud ]</div>
            </div>
        </div>
        <div class="box" style="flex-direction: column; align-items: flex-start; gap: 10px;">
            <h3>Teacher / User Management</h3>
            <div>[ List of Registered Teachers and Assigned Sections ]</div>
            <div class="btn">[ + Add Teacher Account ]</div>
        </div>
    """
}

base_html = """<!DOCTYPE html>
<html>
<head>
    <title>Wireframe - {page_name}</title>
    <link rel="stylesheet" href="wireframe.css">
</head>
<body>
    <div class="wireframe-container">
        <div class="header">
            <div><strong>[ Logo ] ILikeSci System</strong></div>
            <div class="nav">
                <div class="nav-box" onclick="window.location.href='dashboard.html'">Dashboard</div>
                <div class="nav-box" onclick="window.location.href='assessment.html'">Assessments</div>
                <div class="nav-box" onclick="window.location.href='scoreboard.html'">Performance</div>
                <div class="nav-box" onclick="window.location.href='login.html'">[ Logout ]</div>
            </div>
        </div>
        <div class="content">
            {content}
        </div>
        <div style="border-top: 1px solid #000; padding: 10px; text-align: center; font-size: 14px;">
            Management Tools: 
            <a href="students.html">Rosters</a> | 
            <a href="lessons.html">Lessons</a> | 
            <a href="multimedia.html">Multimedia</a> | 
            <a href="games.html">Interactive Modules</a> | 
            <a href="materials.html">Materials</a> | 
            <a href="records.html">Records</a> | 
            <a href="profile.html">Teacher Profile</a> | 
            <a href="admin.html">Admin</a>
        </div>
    </div>
</body>
</html>
"""

for page, content in pages.items():
    with open(os.path.join(out_dir, page), "w") as f:
        f.write(base_html.replace("{page_name}", page.replace(".html", "").title()).replace("{content}", content))

print("Low-fidelity wireframes generated in 'lowpai' folder.")
