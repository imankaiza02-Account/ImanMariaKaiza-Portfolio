/*JS code to hide sections in the html file and display them once their image/logo is clicked*/
//HIDE ALL THE SECTIONS ON THE INITIAL LOAD
function DefaultHide() {
    document.getElementById('mammals-section').style.display = 'none';
    document.getElementById('amphibians-section').style.display = 'none';
    document.getElementById('birds-section').style.display = 'none';
}
//Adding EventListener for the 'DefaultHide()' function
document.addEventListener('DOMContentLoaded', DefaultHide);
//DISPLAYING AND HIDING SECTIONS FOR THE SPECIFIC ANIMAL CLASSES
function DisplaySection(section_id) {
    //HIDES ALL THE ANIMAL CLASSES SECTIONS
    document.getElementById('mammals-section').style.display = 'none';
    document.getElementById('amphibians-section').style.display = 'none';
    document.getElementById('birds-section').style.display = 'none';
    //DISPLAYS FOR THE SPECIFIC ANIMAL CLASS 'sectionID'
    document.getElementById(section_id).style.display = 'block';
}
//IN THE HTML CODE:
// The 'href="javascript:void(0);"' will prevent the page from refreshing when the link is clicked.
// The 'onclick="DisplaySection('section-id')"' will take the section id as specified and will display that section but hide the rest, when the image is clicked.