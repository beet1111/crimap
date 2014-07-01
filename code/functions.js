var browserSupportFlag, window, navigator, alert, document, google;

function search() {
    "use strict";
    var a = document.getElementById("searchBox").value,
        b = document.getElementById("searchButton");
    if (a === "") {
        return false;
    }
    b.value = "searching...";
    (new google.maps.Geocoder()).geocode({
        address: a
    }, function (d, e) {
        if (e === google.maps.GeocoderStatus.OK) {
            var c = d[0].geometry.location;
            window.location = "index.php?lat=" + c.lat() + "&lng=" + c.lng() + "&locate=0";
        } else {
            alert(a + " not found");
        }
        b.value = "search";
    });
    return false;
}

function geoCrime() {
    "use strict";
    function a(b) {
        if (b === false) {
            alert("Your browser doesn't support geolocation.");
        }
    }
    if (navigator.geolocation) {
        browserSupportFlag = true;
        navigator.geolocation.getCurrentPosition(function (b) {
            window.location = "index.php?lat=" + b.coords.latitude + "&lng=" + b.coords.longitude + "&locate=1";
        }, function () {
            a(browserSupportFlag);
        });
    } else {
        browserSupportFlag = false;
        a(browserSupportFlag);
    }
}