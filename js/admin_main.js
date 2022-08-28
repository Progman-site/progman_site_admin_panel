// let technologies = null;
// getTechnologies();
// function getTechnologies() {
//     let formData = new FormData();
//     formData.append('form_name', 'getTechnologies');
//     fetch('admin_api_controller.php', {
//         method: "POST",
//         body: formData,
//     }).then(
//         response => response.json().then(
//             result => {
//                 console.log(result);
//                 if (result) {
//                     technologies = result;
//                 } else {
//                     alert('Не предвиденная ошибка!');
//                 }
//             }
//         ));
// }

document.querySelectorAll('.changer').forEach(key => {
    key.addEventListener('click', event => {
        if (event.target.dataset.task === 'change') {
            event.target.parentElement.parentElement.querySelectorAll('input, textarea, select').forEach(item => {
                if (!item.readOnly) {
                    item.disabled = false;
                }
            });
            event.target.parentElement.lastElementChild.remove();
            event.target.dataset.task = 'save';
            event.target.innerText = 'сохранить'
        }
        else if (event.target.dataset.task === 'save') {
            let formData = new FormData();
            formData.append('form_name', 'updateCertificates');
            if (event.target.dataset.id) {
                formData.append('id', event.target.dataset.id);
            }
            let inputs = event.target.parentElement.parentElement.querySelectorAll('input, select');
            let textareas = event.target.parentElement.parentElement.querySelectorAll('textarea');
            inputs.forEach(item => {
                if (!item.readOnly) {
                    formData.append(item.name, item.value);
                }
            });
            textareas.forEach(item => {
                if (!item.readOnly) {
                    formData.append(item.name, item.value);
                }
            });

            fetch('admin_api_controller.php', {
                method: "POST",
                body: formData
            }).then(
                response => response.json().then(
                    result => {
                        console.log(result);
                        if (result.status === "ok!") {
                            alert(result.data);
                            location.reload();
                        } else {
                            alert('Не предвиденная ошибка!');
                        }
                    }
                ));
        }
    });
});

document.querySelectorAll('.search_field').forEach(elem => {
    elem.addEventListener('input', event => {
        let inputs = event.target.parentElement.parentElement.querySelectorAll('input');
        if (event.target.value.length < 3) {
            inputs.forEach(oneElem => {
                oneElem.style.background = 'white';
                oneElem.value = null;
            });
            return false;
        }
        let formData = new FormData();
        formData.append('form_name', 'userSearch');
        formData.append('field', event.target.dataset.field);
        formData.append('value', event.target.value);
        fetch('admin_api_controller.php', {
            method: "POST",
            body: formData
        }).then(
            response => response.json().then(
                result => {
                    console.log(result);
                    if (result.status === 'ok!') {
                        inputs.forEach(oneElem => {
                            oneElem.style.background = 'white';
                            oneElem.value = null;
                            if (
                                result.data[oneElem.name.split('__')[1]] !== undefined
                                && result.data[oneElem.name.split('__')[1]]
                            ) {
                                oneElem.value = result.data[oneElem.name.split('__')[1]];
                                oneElem.style.background = 'lightgreen';
                            }
                        });
                    } else {
                        inputs.forEach(oneElem => {
                            oneElem.style.background = 'white';
                            oneElem.value = null;
                        });
                    }
                }
            ));
    });
});

document.querySelectorAll('select[name="certificates__course"]').forEach(elem => {
    elem.addEventListener('change', event => {
        let checkboxList = event.target.parentElement.querySelector('.checkbox_list');
        checkboxList.innerHTML = '';
        event.target.childNodes.forEach(option => {
            if (option.selected) {
                event.target.parentElement.querySelector('.course_type').innerHTML = option.dataset.type;
                event.target.parentElement.querySelector('.course_level').innerHTML = option.dataset.level;
                let technologiesIds = option.dataset.technologies_ids.split(',');
                let technologiesDescription = option.dataset.technologies_descriptions.split(',');
                option.dataset.technologies.split(',').forEach((item, key) => {
                    checkboxList.innerHTML += `<label title="${technologiesDescription[key]}"><input type="checkbox" name="technologies__${technologiesIds[key]}" value=0 onchange="this.value = Number(this.checked)">${item}</label>`;
                });
            }
        });
    });
});

document.querySelectorAll('.deleter').forEach(key => {
    key.addEventListener('click', event => {
        let id = event.target.dataset.id;
        if (
            !confirm(`вы действительно хотите удалить сертификат #${id}\n из базы безвозвратно?`)
            && prompt(`Напишите: 'да удалить #${id}'`) !== `да удалить #${id}`
        ) {
            alert("!даление отменено!")
            return false;
        }
        let formData = new FormData();
        formData.append('form_name', 'delCertificate');
        formData.append('id', event.target.dataset.id);

        fetch('admin_api_controller.php', {
            method: "POST",
            body: formData
        }).then(
            response => response.json().then(
                result => {
                    console.log(result);
                    if (result.status === "ok!") {
                        alert(result.data);
                        location.reload();
                    } else {
                        alert('Не предвиденная ошибка!');
                    }
                }
            ));
    });
});

function urlToFile(data, theElement, name = 'certificate'){
    let popup = document.createElement('div');
    popup.style = "width: 99vw; left: 0; position: absolute; z-index: 2;";
    let closer = document.createElement('span');
    closer.style = 'position: absolute; right: 20px; top: 20px; background: white; padding: 15px; cursor: pointer; z-index: 2;';
    closer.innerText = '✖';
    closer.setAttribute('onclick', "this.parentNode.remove()");
    popup.appendChild(closer);
    let img = document.createElement('img');
    img.src = 'data:image/jpeg;base64,' + data;
    img.style = "margine: 40px; width: 99%";
    img.filename="PM_school_cert.png"
    let a = document.createElement('a');
    a.download = "PM_school_cert.png";
    a.href = 'data:image/jpeg;base64,' + data;
    a.innerText = "Download";
    a.style = "font-size: 22px; color:white; position: absolute; left: 50%; transform: translateX(-50%)"
    popup.appendChild(img);
    popup.appendChild(a);
    theElement.appendChild(popup);
    // a.href = data; //Image Base64 Goes here
    // a.download = name + ".jpg"; //File name Here
    // a.click(); //Downloaded file
}

function downloadCertificate(id, theElement) {
    let formData = new FormData();
    formData.append('form_name', 'downloadCertificate');
    formData.append('id', id);
    fetch('admin_api_controller.php', {
        method: "POST",
        body: formData
    }).then(response => response.json().then(result => {
        urlToFile(result.data, theElement)
    })).then(res => console.log(res));
}