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
            event.target.innerText = 'Save'
        }
        else if (event.target.dataset.task === 'save') {
            if (event.target.dataset.id) {
                if (!confirm(`Do you really want to make the changes?`)
                    || prompt('Write: yes') !== `yes`) {
                    alert("All changes have been canceled!")
                    location.reload()
                    return false;
                }
            } else {
                if (!confirm(`Do you really want to create a certificate?`)) {
                    alert("All changes have been canceled!")
                    location.reload()
                    return false;
                }
            }
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
                            // location.reload();
                        } else {
                            alert('An unexpected error!');
                        }
                    }
                ))
        }
    })
})


document.querySelector(".edit_panel").addEventListener('submit', event => {
    event.preventDefault();
    let formData = new FormData();
    formData.append('form_name', 'updateSiteInfo');
    let inputs = event.target.querySelectorAll('input, select');
    let textareas = event.target.querySelectorAll('textarea');
    let tagsCounter = [];
    inputs.forEach(item => {
        if (!item.readOnly && item.dataset.touched === "1") {
            formData.append(item.name, item.value);
            tagsCounter.push(item.name);
        }
    });
    textareas.forEach(item => {
        if (!item.readOnly && item.dataset.touched === "1") {
            formData.append(item.name, item.value);
            tagsCounter.push(item.name);
        }
    });

    if (tagsCounter.length === 0) {
        console.log('no changes');
        return false;
    }
    if (!confirm("Do you really want to change tags? :\n\n" + tagsCounter.join("\n"))) {
        return false;
    }

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
                    alert('An unexpected error!');
                }
            }
        ))
    return false;
})

document.querySelectorAll('.touch_sensitive_input').forEach(event => {
    event.addEventListener('input', event => {
        event.target.dataset.touched = "1";
        event.target.style.background = 'lightgoldenrodyellow';
        event.target.parentElement.parentElement.style.background = 'lightsalmon';
    });
})


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
            !confirm(`Do you really want to remove the certificate(id:${id})\n from database forever?`)
            || prompt(`Write: 'delete #${id}'`) !== `delete #${id}`
        ) {
            alert("Deletion cancelled!")
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
                        alert('An unexpected error!');
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

document.querySelectorAll('#tag_search, #tag_search_with_values').forEach(
    item => item.addEventListener('input', () => searchTags(
        document.querySelector('#tag_search'),
        document.querySelector('#tag_search_with_values').checked
    ))
)

function searchTags(searchField, withValues = false) {
    searchField.style.background = 'white'
    searchField.placeholder = withValues ?
        'Search by piece of existing tag values'
        : 'Search by a tag name or description'

    let value = searchField.value.trim()
    if (value.length < 1) {
        hideAllTags(false)
        return false
    }
    if (value.length < 3) {
        return false;
    }
    searchField.style.background = 'lightgray'
    searchField.style.color = 'black'
    hideAllTags(true)

    let values = value.split(" ")
    if (values.length === 1) {
        let tagsBox = document.querySelector('.edit_panel').querySelector(`#${value}`)
        if (tagsBox) {
            searchField.style.background = 'lightgreen'
            tagsBox.style.display = "block"
            searchField.style.color = 'darkred';
            tagsBox.open = true;
        }
    } else {
        let items = []
        document.querySelector('.edit_panel').querySelectorAll('details').forEach(item => {
            if (!withValues) {
                for (let word of values) {
                    word = word.trim().toLocaleLowerCase()
                    if (word.length < 2) {
                        continue
                    }
                    if (
                        (item.id && item.id.toLocaleLowerCase().includes(word))
                        || (item.dataset.description && item.dataset.description.toLocaleLowerCase().includes(word)
                        )
                    ) {
                        items.push(item)
                    }
                }
            } else {
                item.querySelectorAll('input, textarea').forEach(input => {
                    if (input.value.toLocaleLowerCase().includes(value.toLocaleLowerCase())) {
                        items.push(item)
                    }
                })
            }
        })
        if (items.length > 1) {
            searchField.style.background = 'lightblue'
            items.forEach(item => {item.style.display = "block"})
        } else if (items.length === 1) {
            searchField.style.background = 'lightgreen'
            items[0].style.display = "block"
            items[0].open = true;
        }
    }
}



function hideAllTags(hide = true) {
    document.querySelector('.edit_panel').querySelectorAll('details').forEach(item => {
        if (hide) {
            item.open = false
        }
        item.style.display = hide ? "none" : "block"
    })
}

document.querySelectorAll('form .reset').forEach(item => {
    item.addEventListener('click', event => {
        if (!confirm("Do you really want to reset all changes?")){
            return false
        }
        location.reload()
    })
})
