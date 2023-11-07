//Function is used to get data for the current user and course id provided
function bml_get_users(id){
    //Check if the div and h2 elements exists
    if($('#bml_mylearners_error').length > 0 && $('#bml_mylearners_div').length > 0){
        //Define the div and errorText variables
        const div = $('#bml_mylearners_div')[0];
        const errorText = $('#bml_mylearners_error')[0];
        //Set the display to none
        errorText.style.display = 'none';
        div.style.display = 'none';
        //Create the xhr variable
        const xhr = new XMLHttpRequest();
        //Define properties of the xhr variable
        xhr.open('POST', './../blocks/mylearners/classes/inc/get_users.inc.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        //Function is called when the xhr request is loaded
        xhr.onload = function(){
            //Check if the status is OK (200)
            if(this.status == 200){
                //Parse the JSON response
                const text = JSON.parse(this.responseText);
                if(text['error']){
                    //Display error text
                    errorText.innerText = text['error'];
                    errorText.style.display = 'block';
                } else if(text['return']){
                    //Display the data
                    div.innerHTML = text['return'];
                    div.style.display = 'block';
                } else {
                    //Display error text
                    errorText.innerText = 'No data available';
                    errorText.style.display = 'block';
                }
            } else {
                //Display error text
                errorText.innerText = 'Connection error';
                errorText.style.display = 'block';
            }
        }
        //Sends the POST request
        xhr.send(`id=${id}`);
    }
}