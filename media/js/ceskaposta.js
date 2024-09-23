var phParamsPlgPcsCeskaposta = Joomla.getOptions('phParamsPlgPcsCeskaposta');
var phLangPlgPcsCeskaposta = Joomla.getOptions('phLangPlgPcsCeskaposta');

function phSetCheckboxActive(id){
    document.getElementById(id).checked = true;
}

function phGetCeskapostaSelectedShippingMethod() {
    const infoElements = document.getElementsByName('phshippingopt');
    let selectedShippingMethod = null;
    
    for (let i = 0; i < infoElements.length; i++) {
        if (infoElements[i].checked) {
            selectedShippingMethod = infoElements[i].value;
            break;
        }
    }
    return selectedShippingMethod;
}

function phGetCeskapostaDay(dayNr) {
    let day = phLangPlgPcsCeskaposta['MONDAY'];
    switch(dayNr) {
        case 0: day = phLangPlgPcsCeskaposta['MONDAY']; break;
        case 1: day = phLangPlgPcsCeskaposta['TUESDAY']; break;
        case 2: day = phLangPlgPcsCeskaposta['WEDNESDAY']; break;
        case 3: day = phLangPlgPcsCeskaposta['THURSDAY']; break;
        case 4: day = phLangPlgPcsCeskaposta['FRIDAY']; break;
        case 5: day = phLangPlgPcsCeskaposta['SATURDAY']; break;
        case 6: day = phLangPlgPcsCeskaposta['SUNDAY']; break;
    }
    return day;
}

async function showCeskapostaSelectedPickupPoint(point) {

    let selectedShippingMethodSuffix = '';
    let selectedShippingMethod = phGetCeskapostaSelectedShippingMethod();

    let infoElement = document.getElementById('ceskaposta-point-info');
    if (selectedShippingMethod !== null) {
        selectedShippingMethodSuffix = '-' + selectedShippingMethod;
        infoElement = document.getElementById('ceskaposta-point-info' + selectedShippingMethodSuffix);
    }

    if (point) {
        /* Display Branch info immediately */
        let info = '';
        let photo = '';
        if (phParamsPlgPcsCeskaposta[selectedShippingMethod]['display_branch_photo'] == 1) {
            if (point.zip) {
                let imageUrl = 'https://www.postaonline.cz/documents/13422/6263672/' + point.zip + '_exterior_1_400x300.jpg';
            
                const imageExists = await phCheckImageExists(imageUrl);
                if (imageExists) {            
                    photo = imageUrl;
                    info += '<div class="ph-checkout-zasilkovna-info-photo"><img src="'+imageUrl+'" alt="'+point.name+'" /></div>';
                }
            }
        }

        info += '<div class="ph-checkout-ceskaposta-info-name">' + point.name + ", " + point.zip + " " + point.municipality_name + '</div>';

        let openHours = '';
        if (phParamsPlgPcsCeskaposta[selectedShippingMethod]['display_opening_hours'] == 1) {
            if (point.opening_hours) {
                point.opening_hours.forEach((val, idx) => {
                    if (val) {
                        let values = val['od_do'];
                        if (!Array.isArray(values)) {
                            values = [values];
                        }
                        
                        openHours += '<div><div>' + phGetCeskapostaDay(idx) + '</div><div>' + values[0]['od'] + ' - ' + values[0]['do'];
                        if (values.length > 1) {
                            openHours += ', ' + values[1]['od'] + ' - ' + values[1]['do'];
                        }
                        openHours +=  '</div></div>';
                        
                    }
                });
                info += '<div class="ph-checkout-ceskaposta-info-opening-hours">' + openHours + '</div>';
            }
        }
        
        infoElement.innerHTML = info;
    
        /* Add Branch info to form fields - to store them */
        if (phParamsPlgPcsCeskaposta[selectedShippingMethod]['fields'].length !== 0) {
            for (let index = 0; index < phParamsPlgPcsCeskaposta[selectedShippingMethod]['fields'].length; ++index) {
                const element = phParamsPlgPcsCeskaposta[selectedShippingMethod]['fields'][index];
                let elementId = 'ceskaposta-field-' + element + selectedShippingMethodSuffix;

                if (document.getElementById(elementId)){
                    if (element == 'thumbnail') {
                        if (typeof photo !== 'undefined') {
                            document.getElementById(elementId).value = photo;
                        } else {
                            document.getElementById(elementId).value = '';
                        }
                    } else if (element == 'opening_hours') { 
                        if (phParamsPlgPcsCeskaposta[selectedShippingMethod]['display_opening_hours'] == 1) {
                            document.getElementById(elementId).value = openHours;
                        }
                    } else {
                        document.getElementById(elementId).value = point[element];
                    }
                }
                
            }
        }

    } else {
        infoElement.innerText = phLangPlgPcsCeskaposta['PLG_PCS_SHIPPING_CESKAPOSTA_NONE'];
        /* Add Branch info to form fields - clear all values */
        if (phParamsPlgPcsCeskaposta[selectedShippingMethod]['fields'].length !== 0) {
            for (let index = 0; index < phParamsPlgPcsCeskaposta[selectedShippingMethod]['fields'].length; ++index) {
                const element = phParamsPlgPcsCeskaposta[selectedShippingMethod]['fields'][index];
                var elementId = 'ceskaposta-field-' + element;
                document.getElementById(elementId).value = '';
            }
        }
    }
};


function phCheckImageExists(url) {
    return new Promise((resolve) => {
        const img = new Image();
        img.onload = function() {
            resolve(true);
        };
        img.onerror = function() {
            resolve(false);
        };
        img.src = url;
    });
}

/* Listen to Ceska Posta */
function iframeListener(e) {
    if (!e.origin.startsWith('https://b2c.cpost.cz')) {
        return;
    }

    if (!e.data.message) {
        return;
    }
              
    if (e.data.message === 'pickerResult') {

        showCeskapostaSelectedPickupPoint(e.data.point);
        let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('phPcsCeskaPostaPopup'));
		modal.hide();
    }
}
window.addEventListener('message', iframeListener);

/* Test if method is selected */
document.addEventListener("DOMContentLoaded", function() {
    document.querySelector('.ph-checkout-shipping-save .ph-btn').onclick = function(e) {
        
        let selectedShippingMethodSuffix = '';
        let selectedShippingMethod = phGetCeskapostaSelectedShippingMethod();
        
        if (selectedShippingMethod !== null) {
            selectedShippingMethodSuffix = '-' + selectedShippingMethod;
        }
        
        let elementId = 'ceskaposta-field-id' + selectedShippingMethodSuffix;
        let elementDocId = document.getElementById(elementId);
        let elementDocIdValue = elementDocId.value;

        let ceskapostaCheckbox = document.getElementById('ceskaposta-checkbox-id' + selectedShippingMethodSuffix).value;
        let ceskapostaCheckboxChecked = document.getElementById(ceskapostaCheckbox).checked;

        if (phParamsPlgPcsCeskaposta[selectedShippingMethod]['validate_pickup_point'] == 1 && ceskapostaCheckboxChecked && elementDocIdValue == '') {
            e.preventDefault();
            alert(phLangPlgPcsZasilkovna['PLG_PCS_SHIPPING_CESKAPOSTA_ERROR_PLEASE_SELECT_PICK_UP_POINT']);
            return false;
        }
        
    };
});