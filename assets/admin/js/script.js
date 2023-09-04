
// Create urlParams query string
var urlParams = new URLSearchParams(window.location.search);

// Get value of single parameter
var sectionName = urlParams.get('pageno');

if(sectionName){

    sectionName = parseInt(sectionName);
    var url = 'edit.php?post_type=product&page=product-sync&pageno=' + sectionName;

    
    setTimeout(() => {
        window.location.replace(url);
    }, 5000);

}


// Create urlParams query string
var urlParams = new URLSearchParams(window.location.search);

// Get value of single parameter
var sectionName = urlParams.get('pagenoformeta');

if(sectionName){

    // sectionName = parseInt(sectionName) + 1;
    // var url = 'edit.php?post_type=product&page=product-sync&pagenoformeta=' + sectionName;
    // setTimeout(() => {
    //     window.location.replace(url);
    // }, 3000);
}




// Create urlParams query string
var urlParams = new URLSearchParams(window.location.search);

// Get value of single parameter
var sectionName = urlParams.get('pagenoforloc');

if(sectionName){

    sectionName = parseInt(sectionName) + 1;
    var url = 'edit.php?post_type=product&page=product-sync&pagenoforloc=' + sectionName;
    setTimeout(() => {
        window.location.replace(url);
    }, 3000);
}