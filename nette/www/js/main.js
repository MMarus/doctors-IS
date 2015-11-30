$(function(){
    menu();

    //clock - only homepage
    var pathArray = location.href.split( '/' );
    if(pathArray[pathArray.length-2]=="www")
    {
        window.onload = function() {
            startTime();
        };


        patientDD.addEventListener( 'keyup', function( e )
        {
            if(e.keyCode == 13)//enter
            {
                goPatient(this);
            }
        } );



    }
});


function goPatient(slf)
{
    //delay(200);
    //console.log(slf.children[0].children[0].children[1].children[1].children[1] );
    var sel = document.getElementsByClassName("selected");
    var ind = sel[0].getAttribute("data-original-index");
    var cls = sel[0].className;


    if(cls == "selected" || cls == "selected active" )
    {
        //click exists
        if(ind == 0){return;}
        console.log("OPEN");
        window.open("patient/show/" +  ind, "_self");
        return;
    }
    else if(cls == "selected hide")
    {
        //new
        console.log("NEW");
        window.open("patient/edit", "_self");
        return;
    }





}

function delay(ms) {
    ms += new Date().getTime();
    while (new Date() < ms){}
}

function basepath()
{
    var pth;
    var pathArray = location.href.split( '/' );
    var protocol = pathArray[0];
    var host = pathArray[2];
    pth = protocol + '//' + host;
    for (index = 3, len = pathArray.length; index < len; ++index)
    {
        pth += "/" + pathArray[index];

        if(  pathArray[index] == "www" && pathArray[index-1] == "nette" )
        {
            break;
        }
    }
    return pth;
}


function menu_load()
{
    //XHR
    if (window.XMLHttpRequest) {
        xhr = new XMLHttpRequest();
    }
    else if (window.ActiveXObject) {
        xhr = new ActiveXObject("Msxml2.XMLHTTP");
    }
    else {
        throw new Error("Ajax is not supported by this browser");
    }

    // 2. Define what to do when XHR feed you the response from the server - Start
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status == 200 && xhr.status < 300) {
                document.getElementById("wrapper").className = xhr.responseText;
                console.log(xhr.responseText);
            }
        }
    }
    var post = "action=load";
    console.log(basepath()  + '/myajax.php');
    xhr.open('POST', basepath()  + '/myajax.php');
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send(post);

}


function menu(action)
{
    var data = "no";
    var act = "no";
    var xhr;

    if(action == undefined)
    {
        menu_load();
        return;
    }





    //----- SAVE
    //XHR
    if (window.XMLHttpRequest) {
        xhr = new XMLHttpRequest();
    }
    else if (window.ActiveXObject) {
        xhr = new ActiveXObject("Msxml2.XMLHTTP");
    }
    else {
        throw new Error("Ajax is not supported by this browser");
    }

    // 2. Define what to do when XHR feed you the response from the server - Start
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status == 200 && xhr.status < 300) {
                console.log(xhr.responseText);
            }
        }
    }

    if( action == "save" )
    {
        act = "save";
        data = document.getElementById("wrapper").className;

        var post = "action=" + act + "&" + "data="  + data;
        console.log(post);
        xhr.open('POST', basepath() + '/myajax.php');
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send(post);
    }
}


function startTime() {
    var today = new Date();
    var h = today.getHours();
    var m = today.getMinutes();
    var s = today.getSeconds();
    m = checkTime(m);
    s = checkTime(s);
    document.getElementById('clock').innerHTML =
        h + ":" + m + ":" + s;
    var t = setTimeout(startTime, 500);
}
function checkTime(i) {
    if (i < 10) {i = "0" + i};
    return i;
}




//Toto je na zakliknutie vsetkych checkboxov
$('.toggle-button').click( function () {
    var id = this.id;

    $( '#'+id+'All input[type="checkbox"]' ).prop('checked', this.checked)
})

