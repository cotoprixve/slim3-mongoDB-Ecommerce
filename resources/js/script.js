$(document).ready(function () {
    $('#list').click(function (event) {
        event.preventDefault();
        $('#products .item').addClass('list-group-item');
    });
    $('#grid').click(function (event) {
        event.preventDefault();
        $('#products .item').removeClass('list-group-item');
        $('#products .item').addClass('grid-group-item');
    });
});

function addtocart(id, name, price) {

    if (localStorage.getItem("ids") != null) {
        localStorage.setItem("ids", localStorage.getItem("ids") + ";" + id);
    } else {
        localStorage.setItem("ids", id);
    }

    if (localStorage.getItem("total_price") != null) {
        localStorage.setItem("total_price", Number(localStorage.getItem("total_price")) + Number(price));
    } else {
        localStorage.setItem("total_price", 0);
        localStorage.setItem("total_price", Number(localStorage.getItem("total_price")) + Number(price));
    }

    localStorage.setItem("names", localStorage.getItem("names") + ";" + name);

    localStorage.setItem("no_products", localStorage.getItem("ids").split(";").length);
    document.getElementById("basket").innerHTML = localStorage.getItem("no_products");

}

$(document).ready(function checkout() {
    if (localStorage.ids != null) {
        localStorage.setItem("no_products", localStorage.getItem("ids").split(";").length);
    } else {
        localStorage.setItem("no_products", 0);
    }
    document.getElementById("basket").innerHTML = localStorage.getItem("no_products");
});