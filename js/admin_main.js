document.querySelectorAll('.changer').forEach(key => {
    key.addEventListener('click', event => {
        if (event.target.dataset.task === 'change') {
            event.target.parentElement.parentElement.querySelectorAll('input, textarea, select').forEach(item => {
                if (!item.readOnly && !item.classList.contains('select_readonly')) {
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
                if (!confirm(`Do you really want to create a new one?`)) {
                    alert("All changes have been canceled!")
                    location.reload()
                    return false;
                }
            }
            let formData = new FormData();
            formData.append('form_name', event.target.dataset.api_method);
            if (event.target.dataset.id) {
                formData.append('id', event.target.dataset.id);
            }
            let inputs = event.target.parentElement.parentElement.querySelectorAll('input, select');
            let textareas = event.target.parentElement.parentElement.querySelectorAll('textarea');
            inputs.forEach(item => {
                if (item.name && !item.readOnly && item.type !== 'search') {
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
        formData.append('form_name', event.target.dataset.form_name ?? 'userSearch');
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
            !confirm(`Do you really want to remove the object(id:${id})\n from database forever?`)
            || prompt(`Write: 'delete #${id}'`) !== `delete #${id}`
        ) {
            alert("Deletion cancelled!")
            return false;
        }
        let formData = new FormData();
        formData.append('form_name', event.target.dataset.api_method);
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

function urlToFile(data, theElement, fileName = 'certificate'){
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
    img.filename = `PM_${fileName}.png`
    let a = document.createElement('a');
    a.download = "PM_school_cert.png";
    a.href = 'data:image/jpeg;base64,' + data;
    a.innerText = "Download";
    a.style = "font-size: 22px; color:white; position: absolute; left: 50%; transform: translateX(-50%)"
    popup.appendChild(img);
    popup.appendChild(a);
    theElement.appendChild(popup);
}

function downloadFile(id, theElement, apiMethod) {
    let formData = new FormData();
    formData.append('form_name', apiMethod);
    formData.append('id', id);
    fetch('admin_api_controller.php', {
        method: "POST",
        body: formData
    }).then(response => response.json().then(result => {
        urlToFile(result.data, theElement, apiMethod)
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

document.querySelectorAll('.input_adviser').forEach(item => {
    item.addEventListener('input', event => {
        let searchInput = event.target
        let addButton = event.target.parentElement.querySelector('.add_item') || {}
        addButton.disabled = true
        searchInput.dataset.jsondata = ""
        searchInput.style.background = 'white'
        let listAdviser = event.target.parentElement.parentElement.querySelector('.list_adviser')
        if (listAdviser) {
            listAdviser.remove()
        }
        if (searchInput.dataset.creating === "1" && searchInput.value.length > 2) {
            addButton.disabled = false
        }

        if (searchInput.value.length < 3) {
            return false
        }

        listAdviser = document.createElement("ul")
        listAdviser.classList.add("list_adviser")
        listAdviser.style.width = event.target.offsetWidth + 'px'
        listAdviser.style.left = event.target.offsetLeft + 'px'
        listAdviser.style.top = event.target.offsetTop + event.target.offsetHeight + 'px'

        let formData = new FormData();
        formData.append('form_name', 'adviserSearch')
        formData.append('field', event.target.dataset.field)
        formData.append('table', event.target.dataset.table)
        formData.append('value', event.target.value)

        fetch('admin_api_controller.php', {
            method: "POST",
            body: formData
        }).then(
            response => response.json().then(
               data => {
                   data.data.forEach(item => {
                      let li = document.createElement("li")
                      li.innerText = item.name
                      li.dataset.jsondata = JSON.stringify(item)
                      li.addEventListener('click', event => {
                          searchInput.value = event.target.innerText
                          searchInput.dataset.jsondata = event.target.dataset.jsondata
                          addButton.disabled = false
                          event.target.parentElement.remove()
                          searchInput.style.background = 'lightgreen'
                          listAdviser.innerHTML = ''
                      })
                      listAdviser.appendChild(li)
                   })
                   event.target.after(listAdviser)
               }
            )
        )
    })
})

document.querySelectorAll('.search_editor .add_item').forEach(item => {
    item.addEventListener('click', event => {
        let itemsBox = event.target.parentElement.querySelector('.checkbox_list')
        let inputAdviser = event.target.parentElement.querySelector('.input_adviser')
        let listAdviser = event.target.parentElement.querySelector('.list_adviser')

        inputAdviser.value = inputAdviser.value.trim()

        let inputAdviserData
        if (inputAdviser.dataset.creating === "1" && !inputAdviser.dataset.jsondata && inputAdviser.value.length > 2) {
            if (itemsBox.querySelector(`input[data-name="${inputAdviser.value}"]`)){
                alert(`The new item '${inputAdviser.value}' already exists!`)
                return false
            }
            if(!confirm(`Do you want to create a new item '${inputAdviser.value}'`)) {
                alert('Creation canceled!')
                return false
            }
            inputAdviserData = {
                name: inputAdviser.value,
                description: prompt('Write a description for this item'),
                id: null
            }
        } else {
            inputAdviserData = JSON.parse(inputAdviser.dataset.jsondata)
            if (itemsBox.querySelector(`input[data-id="${inputAdviserData.id}"]`)){
                alert(`This item "${inputAdviserData.name}" already exists!`)
                return false
            }
        }
        if (listAdviser) {
            listAdviser.remove()
        }
        addNewSearchEditorItem(inputAdviser, inputAdviserData, itemsBox)
        inputAdviser.value = null
        inputAdviser.dataset.jsondata = ""
        inputAdviser.style.background = 'white'
        event.target.disabled = true
        if (event.target.parentElement.classList.contains('sub_course')){
            let formData = new FormData()
            formData.append('form_name', 'getCourseTechnologies')
            formData.append('course_id', inputAdviserData.id)
            fetch('admin_api_controller.php', {
                method: "POST",
                body: formData
            }).then(
                response => response.json().then(
                    result => {
                        if (result.status === "ok!") {
                            let technologiesInputAdviser = document.querySelector('.technology .input_adviser')
                            let technologiesCheckboxList = document.querySelector('.technology .checkbox_list')
                            result.data.forEach(item => {
                                if (technologiesCheckboxList.querySelector(`input[data-id="${item.id}"]`)){
                                    alert(`This item "${item.name}" already exists!`)
                                    return false
                                }
                                addNewSearchEditorItem(technologiesInputAdviser, item, technologiesCheckboxList)
                            })
                        } else {
                            alert('An unexpected error!');
                        }
                    }
                ))
        }
    })
})

function addNewSearchEditorItem(inputAdviserElement, inputAdviserData, itemsBox) {
    let childAttributes = JSON.parse(inputAdviserElement.dataset.child_attributes)
    let label = document.createElement('label')
    label.title = inputAdviserData.description
    label.innerHTML += `<strong>${inputAdviserData.name}</strong>`
    let input = document.createElement('input')
    input.name = `${inputAdviserElement.dataset.table}__${childAttributes.id || inputAdviserData.id || `new_${itemsBox.childElementCount}`}`
    input.type = childAttributes.type
    delete childAttributes.type
    for (let key in childAttributes) {
        input.setAttribute(key, childAttributes[key])
    }
    if (inputAdviserData.hours !== undefined) {
        input.setAttribute('value', inputAdviserData.hours)
    }
    input.dataset.id = inputAdviserData.id
    input.dataset.name = inputAdviserData.name
    label.appendChild(input)
    let name = document.createElement('input')
    name.type = 'hidden'
    name.name = `${input.name}_name`
    name.value = inputAdviserData.name
    label.appendChild(name)
    let description = document.createElement('input')
    description.type = 'hidden'
    description.name = `${input.name}_description`
    description.value = inputAdviserData.description
    label.appendChild(description)
    label.innerHTML += `<span class="remover" onclick="this.parentElement.querySelector('input').disabled || this.parentElement.remove()">✖</span>`

    if (inputAdviserElement.dataset.table === 'technologies') {
        if (!childAttributes.id
            && !inputAdviserData.id
        ) {
            const technologiesType = document.createElement('select')
            technologiesType.name = `${input.name}_type`
            technologiesType.style = 'margin-left: 20px;'
            technologiesType.innerHTML = `<option value="frontend">Frontend</option>
                                          <option value="backend">Backend</option>
                                          <option value="devops">Devops</option>
                                        <option value="other" selected>Other</option>`
            label.appendChild(technologiesType)
        } else {
            const technologiesType = document.createElement('span')
            technologiesType.classList.add('sub_info')
            technologiesType.innerText = inputAdviserData.type
            label.appendChild(technologiesType)
        }
    }
    itemsBox.appendChild(label)
}

document.querySelectorAll('.technology_remover').forEach(item => {
    item.addEventListener('click', event => {
        const list = event.target.parentElement.parentElement.parentElement
        if (!confirm(`Do you really want to remove '${event.target.parentElement.querySelector("strong").innerHTML}' technology?`)){
            return false
        }
        const formData = new FormData()
        formData.append('form_name', 'removeTechnology')
        formData.append('id', event.target.dataset.id)
        fetch('admin_api_controller.php', {
            method: "POST",
            body: formData
        }).then(
            response => response.json().then(
                result => {
                    if (result.status === "ok!") {
                        alert(result.data);
                        event.target.parentElement.parentElement.remove()
                        if (list.childElementCount === 0) {
                            location.reload();
                        }
                        list.parentElement.querySelector('summary > span').innerText = list.childElementCount
                    } else {
                        alert('An unexpected error!');
                    }
                }
            ))
    })
})

document.querySelectorAll(
    'select[name="coupons__method"], select[name="coupons__coupon_type_id"]'
).forEach(item => {
    item.addEventListener('change', event => {
        const serialNumberInput = event.target.parentElement.parentElement.parentElement.querySelector(
            'input[name="coupons__serial_number"]'
        )
        const couponDescriptionTextarea = event.target.parentElement.parentElement.parentElement.querySelector(
            'textarea[name="coupons__description"]'
        )
        let couponMethodSelector, couponTypeSelector
        if (event.target.name === 'coupons__coupon_type_id') {
            couponTypeSelector = event.target
            const selectedOption = event.target.querySelector('option:checked')
            couponTypeSelector.dataset.prefix = selectedOption.dataset.prefix
            couponTypeSelector.dataset.title = selectedOption.dataset.title
            couponMethodSelector = event.target.parentElement.parentElement.parentElement.querySelector(
                'select[name="coupons__method"]'
            )
        } else {
            couponTypeSelector = event.target.parentElement.parentElement.parentElement.querySelector(
                'select[name="coupons__coupon_type_id"]'
            )
            couponMethodSelector = event.target
        }

        if (couponMethodSelector.value === 'generated') {
            let prefixPart = couponTypeSelector.dataset.prefix ? `${couponTypeSelector.dataset.prefix}-` : '(Needs Type!)'
            serialNumberInput.value = ""
            serialNumberInput.placeholder = `${prefixPart}XXXXXX`
            serialNumberInput.disabled = true
        } else {
            serialNumberInput.placeholder = "Write a full serial!"
            serialNumberInput.value = ""
            serialNumberInput.disabled = false
        }

        const typePrefixInput = event.target.parentElement.querySelector("input[name='coupon_types__prefix']")
        if (!couponTypeSelector.value) {
            couponDescriptionTextarea.rquired = true
            couponDescriptionTextarea.placeholder = "Write a description for the coupon (required!)"
            typePrefixInput.value = ""
        } else {
            couponDescriptionTextarea.rquired = false
            couponDescriptionTextarea.placeholder = "Coupon description (optional)"
            typePrefixInput.value = couponTypeSelector.dataset.prefix
        }
        console.log(typePrefixInput)
    })
})

document.querySelectorAll('select[name="coupons__coupon_unit_id"]').forEach(item => {
    item.addEventListener('change', event => {
        const couponUnitSelector = event.target
        const selectedOption = event.target.querySelector('option:checked')
        couponUnitSelector.dataset.symbol = selectedOption.dataset.symbol
        couponUnitSelector.dataset.symbol_placement = selectedOption.dataset.symbol_placement
        couponUnitSelector.dataset.formula = selectedOption.dataset.formula

        couponUnitSelector.parentElement.querySelectorAll('.coupon_unit_prefix').forEach(
            item => item.innerText = ''
        )
        couponUnitSelector.parentElement.querySelector(
            `.coupon_unit_prefix_${couponUnitSelector.dataset.symbol_placement}`
        ).innerText = couponUnitSelector.dataset.symbol
        couponUnitSelector.parentElement.parentElement.querySelector('.coupon_unit_formula').innerHTML
            = couponUnitSelector.dataset.formula
    })
})

document.querySelectorAll('input[name="coupons__serial_number"]').forEach(item => {
    item.addEventListener('input', event => {
        if (event.target.value.length > 5) {
            const formData = new FormData()
            formData.append('form_name', 'checkCouponSerialNumber')
            formData.append('serial_number', event.target.value)
            fetch('admin_api_controller.php', {
                method: "POST",
                body: formData
            }).then(
                response => response.json().then(
                    result => {
                        if (result.status === "ok!") {
                            if (result.data) {
                                event.target.style.background = 'red'
                            } else {
                                event.target.style.background = 'lightgreen'
                            }
                        } else {
                            alert('An unexpected error!');
                        }
                    }
                ))
        } else {
            event.target.style.background = 'white'
        }
    })
})
