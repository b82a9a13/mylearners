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
                    //Add a script element to the div
                    const script = document.createElement('script');
                    script.innerHTML = text['script'];
                    div.appendChild(script);
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
//Function is called to render a chart for a sepcific id
function bml_render_chart(id, total, comp, await){
    //Check for valid parameters and check if a element with the given id exists
    if(id.includes('bml_') && total > 0 && comp >= 0 && await >= 0 && $(`#${id}`).length > 0){
        //Create constants to be used in creating the chart
        const canvas = $(`#${id}`)[0];
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const radius = Math.min(centerX, centerY) * 0.9;
        const incomplete = (comp == 0 && await == 0) ? 2 * Math.PI : (1 - ((comp+await) / total)) * 2 * Math.PI;
        //Draw the arc for incomplete competencies
        ctx = canvas.getContext('2d');
        ctx.fillStyle = 'red';
        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.arc(centerX, centerY, radius, 0, incomplete);
        ctx.lineTo(centerX, centerY);
        ctx.fill();
        //Set the awaiting review arc end, and draw the awaiting review if the is any competencies awaiting review
        const awaiting = (comp == 0) ? 2 * Math.PI : (1 - (comp / total)) * 2 * Math.PI;
        if(await > 0){
            ctx.fillStyle = 'orange';
            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, radius, incomplete, awaiting);
            ctx.lineTo(centerX, centerY);
            ctx.fill();
        }
        //Draw the completed arc if there are any completed competencies
        if(comp > 0){
            ctx.fillStyle = 'green';
            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            if(await == 0){
                ctx.arc(centerX, centerY, radius, incomplete, 2 * Math.PI);
            } else if(await != 0){
                ctx.arc(centerX, centerY, radius, awaiting, 2 * Math.PI);
            }
            ctx.lineTo(centerX, centerY);
            ctx.fill();
        }
    }
}