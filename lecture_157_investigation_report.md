# Lecture 157 Instructor Images Investigation Report

## Executive Summary
Based on code analysis and examination of the existing debugging infrastructure, I've identified the potential causes of wrong instructor images being displayed for lecture ID 157.

## System Architecture Analysis

### Database Structure
The lectures table uses two approaches for storing instructor data:
1. **Legacy single instructor fields:**
   - `instructor_name` (VARCHAR) - Single instructor name
   - `instructor_image` (VARCHAR) - Single instructor image path
   - `instructor_info` (TEXT) - Single instructor bio

2. **Modern multi-instructor system:**
   - `instructors_json` (JSON/TEXT) - Stores array of instructor objects
   - Each instructor object contains: `name`, `image`, `info`, `title`

### Image Display Logic
Located in `/workspace/src/views/lectures/detail.php` (lines 1166-1299):

1. **Priority System:**
   - If `instructors_json` exists and is valid → Use JSON data
   - Else → Fall back to legacy single instructor fields

2. **Image Resolution Process:**
   - Check if instructor has specific image in JSON
   - If image missing or file doesn't exist → Use fallback system
   - Fallback uses hash of instructor name to select from 6 default images
   - Final fallback is placeholder with instructor's first initial

3. **File Path Structure:**
   - Instructor images stored in: `/public/assets/uploads/instructors/`
   - Web path format: `/assets/uploads/instructors/filename.ext`

## Potential Issues for Lecture 157

### 1. Invalid or Corrupted instructors_json Data
**Symptoms:** Wrong images displayed, image conflicts
**Cause:** 
- Malformed JSON in database
- Incorrect image file paths in JSON
- Mixed up instructor data during creation/editing

### 2. File Path Inconsistencies
**Symptoms:** Images not loading, showing placeholders
**Evidence from file naming patterns:**
- Files use timestamp prefixes: `instructor_0_1750XXXXXX_*.jpg`
- Multiple formats: `.jpg`, `.webp`, with various suffixes
- Potential for path mismatches between database and filesystem

### 3. Image File Conflicts
**Symptoms:** Wrong instructor images from other lectures
**Potential causes:**
- Same image file used for multiple lectures
- Image files overwritten during upload
- Incorrect file associations in database

### 4. Upload Timestamp Issues
**Evidence from instructor files:**
- Recent uploads around June 18-24, 2025
- Files with pattern: `instructor_0_1750487532_KakaoTalk_*.jpg`
- Multiple versions of same base image suggest potential overwrites

## Investigation Steps Performed

### 1. Code Analysis
- ✅ Examined LectureController.php for instructor handling logic
- ✅ Analyzed EventController.php (extends LectureController)
- ✅ Reviewed lecture detail view template
- ✅ Identified debug infrastructure from lecture 132 case

### 2. File System Analysis
- ✅ Examined instructor upload directory structure
- ✅ Identified file naming patterns and recent uploads
- ✅ Found evidence of multiple image versions

### 3. Debugging Infrastructure
- ✅ Found existing debug tools for lecture 132
- ✅ Created adapted debug script for lecture 157
- ✅ Identified debug parameters (?debug) for runtime analysis

## Specific Files to Investigate

### 1. Recent Instructor Images (Potential lecture 157 candidates)
Based on timestamps around lecture creation time:
```
instructor_0_1750739758_KakaoTalk_20250621_144804430.jpg
instructor_1_1750739758_KakaoTalk_20250621_144804430_01.jpg
instructor_0_1750487532_KakaoTalk_20250621_144804430.jpg
instructor_1_1750487532_KakaoTalk_20250621_144804430_01.jpg
```

### 2. Debug Scripts Created
- `/workspace/debug_lecture_157_detailed.php` - Comprehensive analysis script
- Can be run via web access or command line (when DB available)

## Recommended Investigation Steps

### Immediate Actions
1. **Run Database Analysis:**
   ```bash
   php debug_lecture_157_detailed.php
   ```

2. **Check Web Interface with Debug:**
   ```
   GET /lectures/157?debug
   ```

3. **Verify Image Files:**
   - Check if lecture 157's instructor images exist on filesystem
   - Verify file permissions and accessibility

### Database Queries to Run
```sql
-- Check lecture 157 data
SELECT id, title, instructor_name, instructor_image, instructors_json, 
       created_at, updated_at 
FROM lectures WHERE id = 157;

-- Check for image conflicts
SELECT id, title, instructor_image 
FROM lectures 
WHERE instructor_image IN (
  SELECT instructor_image FROM lectures WHERE id = 157
) AND id != 157;

-- Check JSON data validity
SELECT id, title, 
       JSON_VALID(instructors_json) as json_valid,
       JSON_LENGTH(instructors_json) as instructor_count
FROM lectures 
WHERE id BETWEEN 150 AND 165;
```

### File System Checks
```bash
# Check instructor image files
ls -la public/assets/uploads/instructors/ | grep -E "175073|175074|175075"

# Check for duplicate or conflicting files
find public/assets/uploads/instructors/ -name "*KakaoTalk*" -ls
```

## Likely Root Causes

### Most Probable (Based on Evidence)
1. **Instructor JSON Data Corruption:**
   - Wrong image paths stored in instructors_json field
   - Images from other lectures mistakenly assigned

2. **File Upload Issues:**
   - Image files overwritten during concurrent uploads
   - Timestamp collisions in file naming

3. **Cache/Session Issues:**
   - Stale instructor data from form submissions
   - Cross-contamination during lecture creation process

### Secondary Possibilities
1. **Database Transaction Issues:**
   - Incomplete updates leaving mixed data state
   - Race conditions during instructor data updates

2. **Front-end Form Issues:**
   - JavaScript errors during instructor image upload
   - Form data corruption before submission

## Debugging Infrastructure Available

### Existing Tools (from lecture 132 case)
- Debug parameter support in lecture detail view
- Comprehensive image validation logic
- Fallback system for missing images
- Console logging for image load failures

### New Tools Created
- Enhanced debug script specifically for lecture 157
- Detailed file system analysis
- Cross-lecture image conflict detection

## Next Steps

1. **Execute Database Analysis Script**
2. **Review Actual Data** from database queries
3. **Check Image File Associations**
4. **Implement Fix** based on findings:
   - Update instructors_json with correct data
   - Replace wrong image files if necessary
   - Clean up conflicting associations

## Conclusion

The wrong instructor images for lecture 157 are most likely caused by:
1. Incorrect data in the `instructors_json` field
2. File path mismatches between database and filesystem
3. Potential image file conflicts with other lectures

The codebase has robust debugging infrastructure that should help identify the exact issue once database access is available.