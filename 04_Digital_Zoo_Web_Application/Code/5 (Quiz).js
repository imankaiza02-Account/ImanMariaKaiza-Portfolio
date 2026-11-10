const form = document.getElementById('form');
function Quiz() {
    pointSystem = {
        'fav_season': {
            'winter': 3,    
            'summer': 1,      
            'spring': 2,    
            'autumn': 4     
        },
        'zodiacSign': {
            'aries': 2,    
            'taurus': 3,    
            'gemini': 1,    
            'cancer': 4,    
            'leo': 2,       
            'virgo': 3,     
            'libra': 1,     
            'scorpio': 4,   
            'sagittarius': 2, 
            'capricorn': 3,   
            'aquarius': 1,   
            'pisces': 4       
        },
        'activity': {
            'friends': 1,     
            'exploration': 2, 
            'education': 3,   
            'helping': 4      
        }
}
}
document.addEventListener('DOMContentLoaded', Quiz);
//The section above is just a guideline of the points to be assigned, the points are stored and waiting to be used once the form is submitted.

function ProcessQuiz(event) {
    event.preventDefault();
    //The preventDefault() method cancels the event if it is cancelable, meaning that the default action that belongs to the event will not occur.
    let totalScore = 0;
    //Calculating the score from each question
    const season = document.querySelector('input[name="fav_season"]:checked');
    const zodiacSign = document.querySelector('input[name="zodiacSign"]:checked');
    const activity = document.querySelector('input[name="activity"]:checked');
    if (!season || !zodiacSign || !activity) {
        alert ("Please answer all questions!!!");
        return;
    }
    //Adding the points
    totalScore = totalScore + pointSystem['fav_season'][season.id];
    totalScore = totalScore + pointSystem['zodiacSign'][zodiacSign.id];
    totalScore = totalScore + pointSystem['activity'][activity.id];

    let resultAnimal = '';
    if (totalScore>=3 && totalScore<=5) {
        resultAnimal = 'DOLPHIN';
    }
    else if (totalScore>=6 && totalScore<=8) {
        resultAnimal = 'EAGLE';
    }
    else if (totalScore>=9 && totalScore<=11) {
        resultAnimal = 'BEAR';
    }
    else if (totalScore>=12 && totalScore<=15) {
        resultAnimal = 'WOLF';
    }
    alert (`Your Inner Animal is ${resultAnimal}!!!`)
}
form.addEventListener('submit', ProcessQuiz);
//This section will now set the points to the specific options in the quiz, and will calculate, determine, and output to the user their inner animal based on the point system in the above section