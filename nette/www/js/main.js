$(function(){
    menu();

    //clock - only homepage
    var pathArray = location.href.split( '/' );
    console.log(pathArray);
    if(pathArray.length== 4 )
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

    if(pathArray[4]=="sign")
    {
        var ebox = document.getElementById("errbox");
        var tmp;
        var x = document.getElementsByClassName("error");

        for (var index = 0, len = x.length; index < len; ++index)
        {
            tmp = x[index].textContent;
            tmp = tmp.replace(/\s\s+/g,"");


            x[index].parentNode.removeChild(x[index]);//delete
            ebox.innerHTML =  "<center class='red'>" + tmp + "</center>";



            console.log();
        }



    }
});


function goPatient(slf)
{
    //delay(200);
    //console.log(slf.children[0].children[0].children[1].children[1].children[1] );
    var opt = document.getElementsByClassName("selectpicker")[0].children;
    var sel = document.getElementsByClassName("selected");
    var ind = sel[0].getAttribute("data-original-index");
    ind = opt[ind].getAttribute("value");
    var cls = sel[0].className;
    var dat = sel[0].children[0].children[0].textContent;




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
        sel = document.getElementsByClassName("no-results");
        var n = sel[0].textContent;
        n = n.replace("No results match \"", "");//delete
        n = n.replace("\"", "");//delete
        var post= "";

        if(isNum(n))
        {
            post = "type=rc&data1=" + n;
        }
        else
        {
            post = "type=name";
            var list = n.split( " " );

            if(list.length > 1)
            {
                post += "&data1=" + list[0];
                post += "&data2=" + list[1];
            }
            else
            {
                post += "&data1=" + n;
                post += "&data2=";
            }


        }


        window.open("patient/edit?"  +  post, "_self");
        return;
    }




    function isNum(n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    }





}

function delay(ms) {
    ms += new Date().getTime();
    while (new Date() < ms){}
}

function basepath(xx)
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

    if(xx == "yi")
    {
        return protocol + '//' + host;
    }

    pth = protocol + '//' + host;
    pth = protocol;
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
    var lnk = basepath()  + '/myajax.php';
    console.log(lnk);
    xhr.open('POST', lnk);
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
        var lnk = basepath()  + '/myajax.php';
        console.log(lnk);
        xhr.open('POST', lnk);
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










function plan(slf,mode)
{
    var plans = document.getElementsByName("oneplan");
    //clear
    for (var index = 0, len = plans.length; index < len; ++index)
    {
        plans[index].setAttribute("hide",0);
    }

    switch (mode)
    {

        case 'all':
        {

            for (var index = 0, len = plans.length; index < len; ++index)
            {
                plans[index].setAttribute("hide",0);
            }
            break;
        }



        case 'today':
        {
            var dte = fdate("today","ymd");

            for (var index = 0, len = plans.length; index < len; ++index)
            {
                var xx = plans[index].getElementsByTagName("td");

                for (var ind = 0, len2 = xx.length; ind < len2; ++ind)
                {
                    if(xx[ind].getAttribute("name") == "date")
                    {
                        if(  !(xx[ind].getAttribute("value").indexOf(dte) > -1 )  )
                         {
                            //je to mimo today tak to skovaj !
                             plans[index].setAttribute("hide",1);
                         }
                    }
                }
            }
            break;
        }



        case 'tomorrow':
        {
            var dte = fdate("today","ymd",1);

            for (var index = 0, len = plans.length; index < len; ++index)
            {
                var xx = plans[index].getElementsByTagName("td");

                for (var ind = 0, len2 = xx.length; ind < len2; ++ind)
                {
                    if(xx[ind].getAttribute("name") == "date")
                    {
                        if(  !(xx[ind].getAttribute("value").indexOf(dte) > -1 )  )
                        {
                            //je to mimo today tak to skovaj !
                            plans[index].setAttribute("hide",1);
                        }
                    }
                }
            }
            break;
        }

        case 'notfin':
        {

            for (var index = 0, len = plans.length; index < len; ++index)
            {
                if(plans[index].getAttribute("state") == "done" )
                {
                    plans[index].setAttribute("hide",1);
                }
            }

            break;
        }


        default:
        {
            break;
        }
    }



    //make clear and hide
    var temp = "";
    for (var index = 0, len = plans.length; index < len; ++index)
    {
        var lst = plans[index].classList;
        for(var ind = 0, len2 = lst.length; ind < len2; ++ind)
        {
            if(lst[ind] != "hidden")
            {
                temp += " " + lst[ind];
            }


        }
        plans[index].className = temp;
        temp = "";

        if(plans[index].getAttribute("hide")==1 )
        {
            plans[index].className += " hidden";
        }
    }




    return;
}























//Toto je na zakliknutie vsetkych checkboxov
$('.toggle-button').click( function () {
    var id = this.id;

    $( '#'+id+'All input[type="checkbox"]' ).prop('checked', this.checked)
})









function num2(x)
{
    x = x.toString();
    if(x.length==1)
    {
        return "0"+x;
    }
    return x;

}

function fdate(data,format,shift,shiftmode)
{
    //data in format 2015-05-05
    if(shiftmode == undefined){shiftmode = "day";}
    if(shift == undefined){shift= 0;}

    if(data == "today")
    {
        var today = new Date();
        var day = today.getDate();//1-31 DAY
        var mon = (today.getMonth()+1);//0-11 MONTH
        var yea = today.getFullYear();//2015 YEAR
        data = yea + "-" + num2(mon) + "-" +   num2(day);
    }

    var tmp;


    var mm = parseInt( data.substring(5, 7) );
    var dd = parseInt( data.substring(8, 10) );
    var yy = parseInt( data.substring(0, 4) );

    //if(shiftmode == "day"){dd = dd + shift;    }
    if(shiftmode == "day"){ myDate = new Date( data ); myDate.setDate(myDate.getDate() + shift); mm = myDate.getMonth() + 1; dd = myDate.getDate(); yy = myDate.getFullYear(); }
    if(shiftmode == "month"){mm = mm + shift;} if(mm > 12){mm = 1; yy++;}; if(mm < 1){mm = 12; yy--;};
    if(shiftmode == "year"){yy = yy + shift;}

    mm = num2(mm);
    dd = num2(dd);
    yy = yy.toString();



    switch (format)
    {
        //01-01-2015
        case 'dmy':
        {
            tmp = dd + ". " + mm  + ". " + yy;
            break;
        }

        case 'dm':
        {
            tmp = dd + ". " + mm  + ". ";
            break;
        }



        //2015-01-01
        case 'ymd':
        {
            tmp = yy + "-" + mm + "-" + dd;
            break;
        }

        case 'ym':
        {
            tmp = yy + "-" + mm;
            break;
        }

        //2015-01-01 HH:MM:SS
        case 'ymd+':
        {
            //$tmp = $dto->format('Y-m-d H:i:s');
            break;
        }

        //week day number
        case 'wdnr':
        {
            //$tmp = $dto->format('w');
            break;

        }

        //first day month
        case 'fdm':
        {
            tmp = yy + "-" + mm + "-" + "01";
            break;
        }

        default:

            break;
    }


    return tmp;
}


