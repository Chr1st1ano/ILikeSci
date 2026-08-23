import urllib.request
import json
import sys
import io

sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

API_URL = "http://localhost/ILikeSci/lessons_api.php"

def post_json(data):
    req = urllib.request.Request(
        API_URL,
        data=json.dumps(data).encode('utf-8'),
        headers={'Content-Type': 'application/json'}
    )
    with urllib.request.urlopen(req) as resp:
        return json.loads(resp.read().decode('utf-8'))

def get_json(params_str=""):
    url = f"{API_URL}?{params_str}" if params_str else API_URL
    with urllib.request.urlopen(url) as resp:
        return json.loads(resp.read().decode('utf-8'))

def run_tests():
    print("=== Testing Lesson Builder API Automation ===")
    
    # 1. Test Automated 1-Slide Lesson Creation
    print("\n[1/4] Creating Automated Single-Slide Lesson...")
    single_slide_payload = {
        "action": "create_lesson",
        "grade": "4",
        "quarter": "1",
        "lesson_number": "101",
        "topic": "Automated Test 1-Slide Lesson",
        "objectives": ["Define Matter", "Identify physical states"],
        "slides": [
            {
                "title": "What is Matter?",
                "content": "<p>Matter is anything that has mass and takes up space.</p>",
                "slide_type": "image",
                "media_type": "image",
                "media_url": "https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=800"
            }
        ]
    }
    res1 = post_json(single_slide_payload)
    print("Response:", res1)
    assert res1.get("status") == "success", f"Failed to create 1-slide lesson: {res1}"
    lesson1_id = res1.get("lesson_id")
    print(f"SUCCESS: Single-slide lesson created (ID: {lesson1_id})")
    
    # Verify slides for single slide lesson
    slides_res1 = get_json(f"slides={lesson1_id}")
    print(f"Fetched slides count: {len(slides_res1.get('slides', []))}")
    assert len(slides_res1.get("slides", [])) == 1, "Expected exactly 1 slide!"
    print("SUCCESS: Verified 1 slide stored in database.")

    # 2. Test Automated Multi-Slide Lesson Creation (3 slides)
    print("\n[2/4] Creating Automated Multi-Slide Lesson (3 Slides)...")
    multi_slide_payload = {
        "action": "create_lesson",
        "grade": "4",
        "quarter": "1",
        "lesson_number": "102",
        "topic": "Automated Test Multi-Slide Lesson",
        "objectives": [
            "Explore states of matter",
            "Observe temperature changes",
            "Perform digital simulation"
        ],
        "slides": [
            {
                "title": "Slide 1: Overview",
                "content": "<p>Overview of physical and chemical changes in matter.</p>",
                "slide_type": "content",
                "media_type": "none",
                "media_url": ""
            },
            {
                "title": "Slide 2: Experiment Procedure",
                "content": "<p>Follow safety protocols while heating water.</p>",
                "slide_type": "step",
                "media_type": "step",
                "media_url": "Step 1: Put 100ml water\nStep 2: Heat to 100 deg C\nStep 3: Measure steam"
            },
            {
                "title": "Slide 3: Interactive PhET Simulation",
                "content": "<p>Interact with the molecular simulation below.</p>",
                "slide_type": "interactive",
                "media_type": "interactive",
                "media_url": "https://phet.colorado.edu/sims/html/states-of-matter-basics/latest/states-of-matter-basics_en.html"
            }
        ]
    }
    res2 = post_json(multi_slide_payload)
    print("Response:", res2)
    assert res2.get("status") == "success", f"Failed to create multi-slide lesson: {res2}"
    lesson2_id = res2.get("lesson_id")
    print(f"SUCCESS: Multi-slide lesson created (ID: {lesson2_id})")

    # Verify slides for multi-slide lesson
    slides_res2 = get_json(f"slides={lesson2_id}")
    fetched_slides2 = slides_res2.get('slides', [])
    print(f"Fetched slides count: {len(fetched_slides2)}")
    assert len(fetched_slides2) == 3, f"Expected 3 slides, got {len(fetched_slides2)}"
    print("SUCCESS: Verified 3 slides stored in database.")

    # 3. Test Editing Multi-Slide Lesson to add 2 more slides (5 slides)
    print("\n[3/4] Updating Lesson to 5 Slides...")
    update_payload = {
        "id": lesson2_id,
        "grade": "4",
        "quarter": "1",
        "lesson_number": "102",
        "topic": "Automated Test Multi-Slide Lesson (5 Slides)",
        "objectives": ["Updated objectives for 5-slide lesson"],
        "slides": [
            {"title": f"Slide {i}", "content": f"Content for slide {i}", "slide_type": "content"}
            for i in range(1, 6)
        ]
    }
    req_put = urllib.request.Request(
        API_URL,
        data=json.dumps(update_payload).encode('utf-8'),
        headers={'Content-Type': 'application/json'},
        method='PUT'
    )
    with urllib.request.urlopen(req_put) as resp:
        res3 = json.loads(resp.read().decode('utf-8'))
    print("Response:", res3)
    assert res3.get("status") == "success"
    
    slides_res3 = get_json(f"slides={lesson2_id}")
    print(f"Fetched updated slides count: {len(slides_res3.get('slides', []))}")
    assert len(slides_res3.get("slides", [])) == 5, "Expected 5 slides after update!"
    print("SUCCESS: Verified 5 slides updated in database.")

    # 4. Verify in All Lessons List
    print("\n[4/4] Verifying lessons list retrieval...")
    all_lessons = get_json("grade=4")
    lessons = all_lessons.get("lessons", [])
    print(f"Total Grade 4 lessons retrieved: {len(lessons)}")
    found1 = any(l["id"] == int(lesson1_id) for l in lessons)
    found2 = any(l["id"] == int(lesson2_id) for l in lessons)
    assert found1 and found2, "Created lessons not found in grade 4 lessons list!"
    print("SUCCESS: Verified created test lessons exist in grade 4 list.")

    print("\nALL LESSON BUILDER AUTOMATION TESTS PASSED PERFECTLY!")

if __name__ == "__main__":
    run_tests()
