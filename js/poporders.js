// gestion et refresh du popup
(function(){

    // instanciation Ajax
    let httpRequest = new XMLHttpRequest();

    // delai refresh
    let refreshPopupTime = 0;

    //Recuperation blocs principaux du module
    let itemPopOrders = document.querySelector('#poporders_block_header');
    let img = itemPopOrders.querySelector('.img');
    let data = itemPopOrders.querySelector('.data');

    //recuperation delai refresh popup
    httpRequest.onreadystatechange = function(){

        if (httpRequest.readyState === 4) {
            refreshPopupTime = JSON.parse(httpRequest.responseText);
            console.log(refreshPopupTime);
        }
    }

    httpRequest.open('GET', '/modules/poporders/ajax/order.php', false);
    httpRequest.setRequestHeader('X-Requested-With', 'xmlhttprequestpopup');
    httpRequest.send();

    // Ajout de la croix pour fermer la fenetre
    let aAdd = document.createElement('a');
    itemPopOrders.appendChild(aAdd);

    let aClose = itemPopOrders.getElementsByTagName('a')[0];

    aClose.setAttribute('class', 'xclose');
    aClose.setAttribute('href', '#');

    aClose.innerHTML = 'x';

    // reactualise le popup
    setInterval(function(){
        console.log('refresh');

        httpRequest.onreadystatechange = function(){

            if (httpRequest.readyState === 4) {

                itemPopOrders.style.opacity = '0';

                let response = JSON.parse(httpRequest.responseText);

                setTimeout(function(){

                    for (let key in response) {

                        if (key == 'linkImg') {

                            let imgItem = img.getElementsByTagName('img')[0];
                            imgItem.setAttribute('src', response[key]);
                        } else {

                            let item = data.querySelector('.'+key);
                            let pItem = item.getElementsByTagName('p')[0];
                            pItem.innerHTML = response[key];
                        }
                    }

                    itemPopOrders.style.opacity = '1';
                }, 700);
            }
        }

        httpRequest.open('GET', '/modules/poporders/ajax/order.php', true);
        httpRequest.setRequestHeader('X-Requested-With', 'xmlhttprequestorder');
        httpRequest.send();

    }, refreshPopupTime);

})();

// permet de ferme le popup
(function(){
    let itemPopOrders = document.querySelector('#poporders_block_header');
    let close = itemPopOrders.querySelector('.xclose');

    close.addEventListener('click', function(event){
        event.preventDefault();
        itemPopOrders.parentNode.removeChild(itemPopOrders);
    });

})();