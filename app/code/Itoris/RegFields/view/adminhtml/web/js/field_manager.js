/**
 * Copyright © 2016 ITORIS INC. All rights reserved.
 * See license agreement for details
 */

define([
    'jquery',
    'prototype'
], function (jQuery) {

    if (!Itoris) {
        var Itoris = {};
    }
    Itoris.FieldManager = Class.create();
    Itoris.FieldManager.prototype = {
        sections : [],
        defaultSections : [],
        sectionsHiddenInputs : [],
        sectionsBlocks : [],
        fieldTypes : {},
        translates : {},
        validationTypes : {},
        captchaTypes : {},
        defaultFields: {},
        isIE7 : false,
        isIE8 : false,
        initialize : function(sections, fieldTypes, translates, validationTypes, captchaTypes, defaultFields, skinUrl, defaultSections) {
            this.fieldTypes = fieldTypes;
            this.translates = translates;
            this.validationTypes = validationTypes;
            this.captchaTypes = captchaTypes;
            this.defaultFields = this.prepareDefaultFields(defaultFields);
            this.skinUrl = skinUrl;
            this.defaultSections = defaultSections;
            this.block = $('field-manager-area');
            this.createSections(sections);
            Event.observe($('add-new-section'), 'click', this.addSection.bind(this, false));
            Event.observe($('reset-form-to-default'), 'click', this.resetForm.bind(this));
            this.createPopup();
            Event.observe(document, 'mouseup', this.unregisterActiveField.bind(this));
            Event.observe(document, 'mousemove', this.moveActiveField.bind(this));
            if (Prototype.Browser.IE) {
                var ieVersion = parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5));
                this.isIE7 = ieVersion == 7;
                this.isIE8 = ieVersion == 8;
            }
        },
        createSections : function(sections) {
            if (sections.length) {
                sections = this.orderItems(sections);
                this.sections = sections;
                for (var i = 0; i < sections.length; i++) {
                    if (sections[i]) {
                        this.addSection(sections[i]);
                    }
                }
            }
        },
        resetForm : function() {
            if (confirm(this.translates.resetForm)) {
                this.removeAllSections();
                this.createSections(this.defaultSections);
            }
        },
        prepareDefaultFields : function(fields) {
            for (var key in fields) {
                if (fields[key].items) {
                    for (var i = 0; i < fields[key].items.length; i++) {
                        if (!fields[key].items[i].value) {
                            delete fields[key].items[i];
                        }
                    }
                }
            }

            return fields;
        },
        addSection : function(section, moveTo) {
            if (!section) {
                section = {
                    order     : this.sections.length,
                    cols      : 1,
                    rows      : 1,
                    removable : true,
                    label     : '',
                    fields    : []
                };
            }
            this.sections[section.order] = section;
            if (!section.fields) {
                this.sections[section.order].fields = [];
            }
            var sectionBlock = this.createElement('div', 'section');
            this.sectionsBlocks[section.order] = sectionBlock;
            var hiddenInput = null;
            for (var key in section) {
                if (section[key] && !(section[key] instanceof Array)) {
                    hiddenInput = this.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'sections[' + section.order + ']['+ key +']';
                    hiddenInput.value = section[key];
                    sectionBlock.appendChild(hiddenInput);
                    if (!this.sectionsHiddenInputs[section.order]) {
                        this.sectionsHiddenInputs[section.order] = [];
                    }
                    this.sectionsHiddenInputs[section.order][key] = hiddenInput;
                }
            }
            sectionBlock.appendChild(this.createSectionInfoBlock(section));
            sectionBlock.appendChild(this.createSectionFieldsTable(section));
            if (moveTo) {
                if (this.sections[section.order - 1]) {
                    this.sectionsBlocks[section.order - 1].insert({'after' : sectionBlock});
                } else {
                    this.block.insert({'top' : sectionBlock});
                }
            } else {
                this.block.appendChild(sectionBlock);
            }
        },
        createSectionInfoBlock : function(section) {
            var infoBlock = this.createElement('div', 'info');
            var colsRowsSelects = this.createElement('div', 'cols-rows-selects')
            var colsLabel = this.createElement('span');
            colsLabel.update(this.translates.columns + ':');
            colsRowsSelects.appendChild(colsLabel);
            var colsSelect = this.createElement('select');
            Event.observe(colsSelect, 'change', this.changeSectionTable.bind(this, section.order, 'cols', colsSelect));
            var selectOption = null;
            for (var i = 1; i <= 3; i++) {
                selectOption = this.createElement('option');
                selectOption.value = i;
                selectOption.update(i);
                if (i == section.cols) {
                    selectOption.selected = true;
                }
                colsSelect.appendChild(selectOption);
            }
            colsRowsSelects.appendChild(colsSelect);
            var rowsLabel = this.createElement('span');
            rowsLabel.update(this.translates.rows + ':');
            colsRowsSelects.appendChild(rowsLabel);
            var rowsSelect = this.createElement('select');
            Event.observe(rowsSelect, 'change', this.changeSectionTable.bind(this, section.order, 'rows', rowsSelect));
            var selectOption = null;
            for (var i = 1; i <= 100; i++) {
                selectOption = this.createElement('option');
                selectOption.value = i;
                selectOption.update(i);
                if (i == section.rows) {
                    selectOption.selected = true;
                }
                rowsSelect.appendChild(selectOption);
            }
            colsRowsSelects.appendChild(rowsSelect);
            infoBlock.appendChild(colsRowsSelects);
            var sectionLabel = this.createElement('span', 'float-left');
            sectionLabel.update(this.translates.sectionLabel + ': ');
            infoBlock.appendChild(sectionLabel);
            var sectionLabelInput = this.createElement('input', 'required-entry label-input');
            Event.observe(sectionLabelInput, 'change', this.changeData.bind(this,section.order, 'label', 'section', sectionLabelInput));
            if (section.label) {
                sectionLabelInput.value = section.label;
            }
            infoBlock.appendChild(sectionLabelInput);
            var moveUpLink = this.createElement('span', 'action-link');
            Event.observe(moveUpLink, 'click', this.moveSection.bind(this, section.order, true));
            infoBlock.appendChild(moveUpLink);
            var delimiter = this.createElement('span');
            infoBlock.appendChild(delimiter);
            if ((section.order - 1 >= 0) && this.sections[section.order - 1]) {
                moveUpLink.update(this.translates.moveUp);
                if (this.sections[section.order + 1]) {
                    delimiter.update(' | ');
                }
            } else {
                moveUpLink.removeClassName('action-link');
            }
            var moveDownLink = this.createElement('span', 'action-link');
            moveDownLink.update(this.translates.moveDown);
            Event.observe(moveDownLink, 'click', this.moveSection.bind(this, section.order, false));
            infoBlock.appendChild(moveDownLink);
            if (!this.sections[parseInt(section.order) + 1]) {
                delimiter.update();
                moveDownLink.update();
            }
            this.sections[section.order].moveUp = moveUpLink;
            this.sections[section.order].delimiter = delimiter;
            this.sections[section.order].moveDown = moveDownLink;
            if (!this.sections[section.order + 1] && this.sections[section.order - 1]) {
                this.sections[section.order - 1].moveUp.show();
                if (this.sections[section.order - 2]) {
                    this.sections[section.order - 1].delimiter.update(' | ');
                    this.sections[section.order - 1].moveDown.update(this.translates.moveDown);
                }
            }
            if (this.isSectionRemovable(section)) {
                var removeLink = this.createElement('span', 'action-link');
                removeLink.update(this.translates.remove);
                infoBlock.appendChild(removeLink);
                Event.observe(removeLink, 'click', this.removeSection.bind(this, section.order));
            }
            return infoBlock;
        },
        isSectionRemovable : function(section) {
            var removable = true;
            if (section['fields']) {
                for (var i = 0; i < section['fields'].length; i++) {
                    if (section['fields'][i]) {
                        if (!section['fields'][i]['removable']) {
                            removable = false;
                            break;
                        }
                    }
                }
            }
            this.sections[section.order].removable = removable;
            return removable;
        },
        changeSectionTable : function(sectionOrder, type, selectBox) {
            var section = this.sections[sectionOrder];
            var newValue = parseInt(selectBox.value);
            var tempField = null;
            var tempFields = [];
            if (type == 'cols') {
                if (parseInt(newValue) < parseInt(section.cols)) {
                    for (var i = 1; i <= section.rows; i++) {
                        for (var j = 1; j <= section.cols; j++) {
                            var num = (i-1) * section.cols + j;
                            var correction = section.cols * (i - 1);
                            if ((num - correction) > newValue && section['fields'][num]) {
                                alert(this.translates.cannotResizeTable);
                                selectBox.value = section[type];
                                return;
                            }
                        }
                    }
                }
                for (var i = 1; i <= section.rows; i++) {
                    for (var j = 1; j <= section.cols; j++) {
                        var num = (i-1) * section.cols + j;
                        var correction = (newValue - section.cols) * (i - 1);
                        if (section['fields'][num]) {
                            tempField = section['fields'][num];
                            tempField.order = parseInt(tempField.order) + correction;
                            tempFields[tempField.order] = tempField;
                        }
                    }
                }
                section['fields'] = tempFields;
            }
            if (type == 'rows') {
                for (var i = parseInt(newValue) + 1; i <= section.rows; i++) {
                    for (var j = 1; j <= section.cols; j++) {
                        var num = (i-1) * section.cols + j;
                        if (section['fields'][num]) {
                            alert(this.translates.cannotResizeTable);
                            selectBox.value = section[type];
                            return;
                        }
                    }
                }
            }
            section[type] = newValue;
            this.sectionsBlocks[sectionOrder].remove();
            delete this.sectionsHiddenInputs[sectionOrder];
            this.addSection(section, true);
        },
        removeSection : function(sectionOrder) {
            if (confirm(this.translates.removeSection)) {
                // Remove "Move Down" link
                if(this.sectionsBlocks.length - 1 == sectionOrder){
                    var newLastSelection = this.sectionsBlocks[sectionOrder-1];
                    var actionLinks = newLastSelection.select('.info .action-link');
                    for (var i = 0; i < actionLinks.length ; i++) {
                        if(actionLinks[i].innerHTML == 'Move Down'){
                            if(actionLinks[i].previous().innerHTML == " | ") actionLinks[i].previous().remove();
                            actionLinks[i].remove();
                        }
                    }
                }
                var sectionsAfter = [];
                var section = null;
                for (var i = sectionOrder; i < this.sections.length; i++) {
                    if (this.sections[i]) {
                        if (i != sectionOrder) {
                            section = this.sections[i];
                            section.order--;
                            sectionsAfter.push(section);
                        }
                        this.sectionsBlocks[i].remove();
                        delete this.sectionsBlocks[i];
                        delete this.sectionsHiddenInputs[i];
                        delete this.sections[i];
                    }
                }
                this.sections.length--;
                for (i = 0; i < sectionsAfter.length; i++) {
                    this.addSection(sectionsAfter[i]);
                }
            }
        },
        removeAllSections : function() {
            for (var i = 0; i < this.sections.length; i++) {
                if (this.sections[i]) {
                    this.sectionsBlocks[i].remove();
                    delete this.sectionsBlocks[i];
                    delete this.sectionsHiddenInputs[i];
                    delete this.sections[i];
                }
            }
            this.sections = [];
        },
        /**
         * Move selected section up or down
         *
         * @param sectionOrder
         * @param moveTo = true (move section up), false (move section down)
         */
        moveSection : function(sectionOrder, moveTo) {
            var nextPos = sectionOrder;
            (moveTo) ? --nextPos : ++nextPos;
            var currentPosSection = this.sections[sectionOrder];
            this.sectionsBlocks[sectionOrder].remove();
            delete this.sectionsHiddenInputs[sectionOrder];
            var nextPosSection = this.sections[nextPos];
            this.sectionsBlocks[nextPos].remove();
            delete this.sectionsHiddenInputs[sectionOrder];
            currentPosSection.order = nextPos;
            nextPosSection.order = sectionOrder;
            if (moveTo) {
                this.addSection(currentPosSection, true);
                this.addSection(nextPosSection, true);
            } else {
                this.addSection(nextPosSection, true);
                this.addSection(currentPosSection, true);
            }
        },
        changeData : function(sectionOrder, key, type, inputField) {
            switch (type) {
                case 'section' : this.sections[sectionOrder].label = inputField.value;
                    if (!this.sectionsHiddenInputs[sectionOrder][key]) {
                        var inputKey = this.createElement('input');
                        inputKey.type = 'hidden';
                        inputKey.name = 'sections[' + sectionOrder + ']['+ key +']';
                        this.sectionsHiddenInputs[sectionOrder][key] = inputKey;
                        this.sectionsBlocks[sectionOrder].appendChild(inputKey);
                    }
                    this.sectionsHiddenInputs[sectionOrder][key].value = inputField.value;
                    break;
            }
        },
        createSectionFieldsTable : function(section) {
            var fieldsTable = this.createElement('table');
            var row = null;
            var col = null;
            var box = null;
            var fieldBlock = null;
            var num = 0;
            if (section.fields) {
                section.fields = this.orderItems(section.fields);
            }
            var tableTBody = this.createElement('tbody');
            fieldsTable.appendChild(tableTBody);
            for (var i = 1; i <= section.rows; i++) {
                row = this.createElement('tr');
                for (var j = 1; j <= section.cols; j++) {
                    col = this.createElement('td');
                    num = (i-1) * section.cols + j;
                    var editButton = this.createElement('div', 'edit-button');
                    editButton.hide();
                    Event.observe(editButton, 'mouseover', this.showElm.bind(this, editButton));
                    Event.observe(editButton, 'mousedown', this.editField.bind(this, section.order, num, col));
                    if (section.fields && section.fields[num]) {
                        fieldBlock = this.createField(section.fields[num], section.order, col);
                        fieldBlock.insert({'top' : editButton});
                        col.appendChild(fieldBlock);
                        var dragDrop = this.createElement('div', 'drag-n-drop');
                        fieldBlock.appendChild(dragDrop);
                    } else {
                        col.addClassName('empty');
                        fieldBlock = this.createElement('div', 'field-container');
                        fieldBlock.appendChild(editButton);
                        col.appendChild(fieldBlock);
                        //box.appendChild(this.createEmptyCeil());
                    }
                    col.section = section.order;
                    col.form = num;
                    Event.observe(col, 'mouseover', this.showElm.bind(this, editButton));
                    Event.observe(col, 'mouseout', this.hideElm.bind(this, editButton));
                    row.appendChild(col);
                }
                tableTBody.appendChild(row);
            }
            return fieldsTable;
        },
        showElm : function(elm) {
            elm.show();
        },
        hideElm : function(elm) {
            elm.hide();
        },
        createEmptyCeil : function() {

        },
        editField : function(sectionOrder, fieldOrder, ceil) {
            this.doNotMoveField = true;
            this.showPopup();
            this.activeField = null;
            var fieldConfig = this.sections[sectionOrder]['fields'] && this.sections[sectionOrder]['fields'][fieldOrder];
            this.tempFieldConfig = fieldConfig || { 'order' : fieldOrder, 'removable' : true};
            if (!this.tempFieldConfig.removable) {
                this.popup.defaultFieldsDropdown.hide();
            } else {
                this.popup.defaultFieldsDropdown.show();
            }
            this.popup.config.update();
            this.popup.config.appendChild(this.createFieldConfig(sectionOrder, fieldConfig));
            this.createFiledConfigButtons(sectionOrder, fieldConfig, ceil, fieldOrder);
        },
        createFiledConfigButtons : function(sectionOrder, fieldConfig, ceil, fieldOrder) {
            var box = this.popup.buttons;
            box.update();
            if (fieldConfig && fieldConfig.removable) {
                var removeButton = this.createButton(this.translates.remove, 'float-right');
                Event.observe(removeButton, 'click', this.removeField.bind(this, sectionOrder, fieldOrder, ceil));
                box.appendChild(removeButton);
                //removeButton.hide();
                //setTimeout(function(){removeButton.show()}, 1000);
            }
            var applyButton = this.createButton(this.translates.apply, 'float-left');
            Event.observe(applyButton, 'click', this.changeFieldData.bind(this, sectionOrder, fieldOrder, ceil));
            box.appendChild(applyButton);
            var cancelButton = this.createButton(this.translates.cancel, 'float-left');
            Event.observe(cancelButton, 'click', this.hidePopup.bind(this));
            box.appendChild(cancelButton);
            return box;
        },
        removeField : function(sectionOrder, fieldOrder, ceil) {
            if (confirm(this.translates.removeField)) {
                this.activeField = null;
                delete this.sections[sectionOrder]['fields'][fieldOrder];
                this.removeFieldConfig(sectionOrder, fieldOrder);
                delete this.sectionsHiddenInputs[sectionOrder]['fields'][fieldOrder];
                this.tempFieldConfig = null;
                this.hidePopup();
                var editButton = this.createElement('div', 'edit-button');
                editButton.hide();
                Event.observe(editButton, 'mouseover', this.showElm.bind(this, editButton));
                Event.observe(editButton, 'mousedown', this.editField.bind(this, sectionOrder, fieldOrder, ceil));
                var fieldBlock = this.createElement('div', 'field-container');
                fieldBlock.appendChild(editButton);
                ceil.update();
                ceil.addClassName('empty');
                ceil.appendChild(fieldBlock);
                Event.observe(ceil, 'mouseover', this.showElm.bind(this, editButton));
                Event.observe(ceil, 'mouseout', this.hideElm.bind(this, editButton));
            }
        },
        changeFieldData : function(sectionOrder, fieldOrder, ceil) {
            if (this.fieldConfigValid()) {
                this.activeField = null;
                ceil.update();
                if (!this.sections[sectionOrder]['fields']) {
                    this.sections[sectionOrder]['fields'] = [];
                }
                this.sections[sectionOrder]['fields'][fieldOrder] = this.tempFieldConfig;
                this.removeFieldConfig(sectionOrder, fieldOrder);
                var fieldBlock = this.createField(this.tempFieldConfig, sectionOrder, ceil);
                var dragDrop = this.createElement('div', 'drag-n-drop');
                fieldBlock.appendChild(dragDrop);
                var editButton = this.createElement('div', 'edit-button');
                editButton.hide();
                Event.observe(editButton, 'mouseover', this.showElm.bind(this, editButton));
                Event.observe(editButton, 'mousedown', this.editField.bind(this, sectionOrder, fieldOrder, ceil));
                fieldBlock.appendChild(editButton);
                Event.observe(ceil, 'mouseover', this.showElm.bind(this, editButton));
                Event.observe(ceil, 'mouseout', this.hideElm.bind(this, editButton));
                ceil.appendChild(fieldBlock);
                ceil.removeClassName('empty');
                this.tempFieldConfig = null;
                this.hidePopup();
            }
        },
        removeFieldConfig : function(sectionOrder, fieldOrder) {
            if (this.sectionsHiddenInputs[sectionOrder]['fields'] && this.sectionsHiddenInputs[sectionOrder]['fields'][fieldOrder]) {
                if (this.sectionsHiddenInputs[sectionOrder]['fields'][fieldOrder]['items']) {
                    for (var i = 0; i < this.sectionsHiddenInputs[sectionOrder]['fields'][fieldOrder]['items'].length; i++) {
                        if (this.sectionsHiddenInputs[sectionOrder]['fields'][fieldOrder]['items'][i] instanceof Array) {
                            for (var key in this.sectionsHiddenInputs[sectionOrder]['fields'][fieldOrder]['items'][i]) {
                                if (typeof this.sectionsHiddenInputs[sectionOrder]['fields'][fieldOrder]['items'][i][key] == 'object') {
                                    if (this.sectionsHiddenInputs[sectionOrder]['fields'][fieldOrder]['items'][i][key].parentNode) {
                                        this.sectionsHiddenInputs[sectionOrder]['fields'][fieldOrder]['items'][i][key].remove();
                                    }
                                }
                            }
                        }
                    }
                }
                for (var key in this.sectionsHiddenInputs[sectionOrder]['fields'][fieldOrder]) {
                    if (typeof this.sectionsHiddenInputs[sectionOrder]['fields'][fieldOrder][key] == 'object' && !(this.sectionsHiddenInputs[sectionOrder]['fields'][fieldOrder][key] instanceof Array)) {
                        if (this.sectionsHiddenInputs[sectionOrder]['fields'][fieldOrder][key].parentNode) {
                            this.sectionsHiddenInputs[sectionOrder]['fields'][fieldOrder][key].remove();
                        }
                    }
                }
            }
        },
        createButton : function(label, className) {
            var button = this.createElement('button', 'scalable ' + className);
            button.writeAttribute('type', 'button');
            var spanLabel = this.createElement('span');
            spanLabel.update(label);
            button.appendChild(spanLabel);
            return button;
        },
        fieldConfigValid : function() {
            var valid = true;
            valid = this.validateElements(this.popup.config.select('.required-entry'), valid);
            valid = this.validateElements(this.popup.config.select('.validate-one-required-by-name'), valid);
            valid = this.validateElements(this.popup.config.select('.validate-digits'), valid);
            valid = this.validateElements(this.popup.config.select('.validate-alphanum'), valid);
            valid = this.validateElements(this.popup.config.select('.validate-dbname'), valid);
            valid = this.validateElements(this.popup.config.select('.validate-max-digit-35'), valid);
            return valid;
        },
        validateElements : function(elms, valid) {
            var elmValid = true;
            for (var i = 0; i < elms.length; i++) {
                if (elms[i]) {
                    elmValid = Validation.validate(elms[i]);
                    valid = !valid ? valid : elmValid;
                }
            }
            return valid;
        },
        createFieldConfig : function(sectionOrder, config) {
            var configBlock = this.createElement('div', 'options');
            var fieldOptions = null;
            this.tempFieldConfig.sectionOrder = sectionOrder;
            if (config) {
                switch (parseInt(config.type)) {
                    case this.fieldTypes.input_box:
                        this.createInputBoxOptions(sectionOrder, configBlock, config);
                        break;
                    case this.fieldTypes.password_box:
                        this.createPasswordBoxOptions(sectionOrder, configBlock, config);
                        break;
                    case this.fieldTypes.checkbox:
                        this.createCheckboxOptions(sectionOrder, configBlock, config);
                        break;
                    case this.fieldTypes.radio:
                        this.createRadioOptions(sectionOrder, configBlock, config);
                        break;
                    case this.fieldTypes.select_box:
                        this.createSelectBoxOptions(sectionOrder, configBlock, config);
                        break;
                    case this.fieldTypes.list_box:
                        this.createListBoxOptions(sectionOrder, configBlock, config);
                        break;
                    case this.fieldTypes.multiselect_box:
                        this.createMultiSelectBoxOptions(sectionOrder, configBlock, config);
                        break;
                    case this.fieldTypes.textarea:
                        this.createTextareaOptions(sectionOrder, configBlock, config);
                        break;
                    case this.fieldTypes.static_text:
                        this.createStaticTextOptions(sectionOrder, configBlock, config);
                        break;
                    case this.fieldTypes.file:
                        this.createFileOptions(sectionOrder, configBlock, config);
                        break;
                    case this.fieldTypes.captcha:
                        this.createCaptchOptions(sectionOrder, configBlock, config);
                        break;
                    case this.fieldTypes.date:
                        this.createDateOptions(sectionOrder, configBlock, config);
                        break;
                }
            } else {
                this.createInputBoxOptions(sectionOrder, configBlock);
                this.tempFieldConfig.type = this.fieldTypes.input_box;
            }
            //configBlock.appendChild(fieldOptions);
            return configBlock;
        },
        editFieldData : function(key, valueElm, isInt) {
            var value = valueElm.value;
            if (isInt) {
                value = parseInt(value);
                if (key == 'min_required') {
                    var itemsCount = this.countTempItems();
                    if (value > itemsCount) {
                        value = itemsCount;
                        valueElm.value = value;
                        alert(this.translates.minRequiredCheckboxes);
                    }
                }
            }
            this.tempFieldConfig[key] = value;
        },
        countTempItems : function() {
            var count = 0;
            for (var i = 0; i < this.tempFieldConfig.items.length; i++) {
                if (this.tempFieldConfig.items[i]) {
                    count++;
                }
            }
            return count;
        },
        changeFieldType : function(sectionOrder, typeElm) {
            var fieldConfig = {
                'label'     : this.tempFieldConfig['label'] ? this.tempFieldConfig.label : '',
                'removable' : this.tempFieldConfig.removable,
                'order'     : this.tempFieldConfig.order
            };
            this.tempFieldConfig = fieldConfig;
            this.tempFieldConfig.type = parseInt(typeElm.value);
            this.popup.config.update();
            this.popup.config.appendChild(this.createFieldConfig(sectionOrder, this.tempFieldConfig));
        },
        loadDefaultField : function(dropdown) {
            var fieldConfig = this.defaultFields[dropdown.value];
            if (fieldConfig) {
                var sectionOrder = this.tempFieldConfig.sectionOrder;
                fieldConfig.order = this.tempFieldConfig.order;
                this.tempFieldConfig = fieldConfig;
                this.popup.config.update();
                this.popup.config.appendChild(this.createFieldConfig(sectionOrder, this.tempFieldConfig));
            }
        },
        createFieldTypesSelect : function(sectionOrder, configBlock, config) {
            var typeRow = this.createElement('div', 'row');
            configBlock.appendChild(typeRow);
            var typeLabel = this.createElement('div', 'label');
            typeRow.appendChild(typeLabel);
            typeLabel.update(this.translates.fieldType + ':');
            var typeValue = this.createElement('div', 'value');
            typeRow.appendChild(typeValue);
            var typeValueSelect = this.createElement('select');
            typeValue.appendChild(typeValueSelect);
            var typeValueSelectOption = null;
            for (var key in this.fieldTypes) {
                if (this.fieldTypes[key]) {
                    typeValueSelectOption = this.createElement('option');
                    typeValueSelectOption.value = this.fieldTypes[key];
                    typeValueSelectOption.update(this.translates[key]);
                    typeValueSelect.appendChild(typeValueSelectOption);
                    typeValueSelectOption.selected = (config && config.type == this.fieldTypes[key]);
                }
            }
            Event.observe(typeValueSelect, 'change', this.changeFieldType.bind(this, sectionOrder, typeValueSelect));
            if (config && (!config.removable || config.isDefault)) {
                typeValueSelect.disabled = true;
            }
        },
        createLabelInput : function(sectionOrder, configBlock, config) {
            var labelRow = this.createElement('div', 'row');
            configBlock.appendChild(labelRow);
            var labelLabel = this.createElement('div', 'label');
            labelRow.appendChild(labelLabel);
            labelLabel.update(this.translates.label + ':');
            var labelValue = this.createElement('div', 'value');
            labelRow.appendChild(labelValue);
            var labelValueInput = this.createElement('input');
            labelValueInput.name = Math.random();
            if (this.isIE7) {
                labelValueInput.addClassName('ie7-margin');
            }
            if (config && config.label) {
                labelValueInput.value = config.label;
            }
            Event.observe(labelValueInput, 'change', this.editFieldData.bind(this, 'label', labelValueInput, false));
            labelValue.appendChild(labelValueInput);
        },
        createRequiredSelect : function(sectionOrder, configBlock, config) {
            var requiredRow = this.createElement('div', 'row');
            configBlock.appendChild(requiredRow);
            var requiredLabel = this.createElement('div', 'label');
            requiredRow.appendChild(requiredLabel);
            requiredLabel.update(this.translates.required + ':');
            var requiredValue = this.createElement('div', 'value');
            requiredRow.appendChild(requiredValue);
            var requiredValueSelect = this.createElement('select');
            requiredValue.appendChild(requiredValueSelect);
            var requiredValueSelectOption = null;
            for (var i = 0; i <= 1; i++) {
                key = i ? 'yes' : 'no';
                requiredValueSelectOption = this.createElement('option');
                requiredValueSelectOption.value = i;
                requiredValueSelectOption.update(this.translates[key]);
                requiredValueSelect.appendChild(requiredValueSelectOption);
                if (i && config && config.required) {
                    requiredValueSelectOption.selected = true;
                }
            }
            if (config && config.requiredFixed) {
                requiredValueSelect.disabled = true;
            }
            Event.observe(requiredValueSelect, 'change', this.editFieldData.bind(this, 'required', requiredValueSelect, true));
            if (config && !config.removable) {
                requiredValueSelect.disabled = true;
            }
        },
        createItemsOptions : function(sectionOrder, configBlock, config) {
            var configRow = this.createElement('div', 'row');
            configBlock.appendChild(configRow);
            var configLabel = this.createElement('div', 'label');
            configRow.appendChild(configLabel);
            configLabel.update(this.translates.quantity + ':');
            var configValue = this.createElement('div', 'value');
            configRow.appendChild(configValue);

            var requiredBox = this.createElement('div', 'float-right');
            configValue.appendChild(requiredBox);
            var requiredLabel = this.createElement('span', 'text-label');
            if (config.type == this.fieldTypes.checkbox) {
                requiredLabel.update(this.translates.minRequired + ': ');
            } else {
                requiredLabel.update(this.translates.required + ': ');
            }
            requiredBox.appendChild(requiredLabel);
            if (config.type == this.fieldTypes.checkbox) {
                var requiredInput = this.createElement('input', 'small-input');
                requiredInput.addClassName('min-required');
                requiredInput.type = 'text';
                if (config && config.min_required) {
                    requiredInput.value = config.min_required;
                } else {
                    requiredInput.value = 0;
                }
                Event.observe(requiredInput, 'change', this.editFieldData.bind(this, 'min_required', requiredInput, true));
            } else {
                var requiredInput = this.createElement('select');
                var requiredOption = null;
                for (var i = 0; i <= 1; i++) {
                    requiredOption = this.createElement('option');
                    requiredOption.value = i;
                    requiredOption.update(i ? this.translates.yes : this.translates.no);
                    if (config && config.required && i) {
                        requiredOption.selected = true;
                    }
                    requiredInput.appendChild(requiredOption);
                }
                Event.observe(requiredInput, 'change', this.editFieldData.bind(this, 'required', requiredInput, true));
            }

            requiredBox.appendChild(requiredInput);

            var rowsSelect = this.createElement('select');
            this.rowsSelect = rowsSelect;
            configValue.appendChild(rowsSelect);
            var rowsSelectOption = null;
            var rowsCount = 1;
            if (config) {
                if (config.items) {
                    rowsCount = 0;
                    for (var i = 0; i < config.items.length; i++) {
                        if (config.items[i]) {
                            rowsCount++;
                        }
                    }
                }
            }
            for (var i = 1; i <= (rowsCount > 50 ? rowsCount : 50); i++) {
                rowsSelectOption = this.createElement('option');
                rowsSelectOption.value = i;
                rowsSelectOption.update(i);
                rowsSelect.appendChild(rowsSelectOption);
                if (rowsCount == i) {
                    rowsSelectOption.selected = true;
                }
            }
            var itemsRow = this.createElement('div', 'row');
            configBlock.appendChild(itemsRow);
            var itemsTable = this.createElement('table');
            itemsRow.appendChild(itemsTable);
            var tableTBody = this.createElement('tbody');
            itemsTable.appendChild(tableTBody);
            var item = null;
            var itemsTableHead = this.createElement('tr');
            tableTBody.appendChild(itemsTableHead);
            var itemsTableHeadLabel = this.createElement('td', 'text-label');
            itemsTableHead.appendChild(itemsTableHeadLabel);
            itemsTableHeadLabel.update(this.translates.itemLabel + ':');
            var itemsTableHeadValue = this.createElement('td', 'text-label');
            itemsTableHead.appendChild(itemsTableHeadValue);
            itemsTableHeadValue.update(this.translates.itemValue + ':');
            var itemsTableHeadChecked = this.createElement('td', 'text-label');
            itemsTableHead.appendChild(itemsTableHeadChecked);
            var checkedText = this.translates.selected;
            if (config.type == this.fieldTypes.checkbox || config.type == this.fieldTypes.radio) {
                checkedText = this.translates.checked;
            }
            itemsTableHeadChecked.update(checkedText);
            var emptyCeil = this.createElement('td');
            itemsTableHead.appendChild(emptyCeil);
            emptyCeil = this.createElement('td');
            itemsTableHead.appendChild(emptyCeil);
            if (config && !config.items) {
                config.items = [];
            }
            var itemConfig = null;
            for (i = 1; i <= rowsCount; i++) {
                if (config) {
                    if (!config.items[i]) {
                        config.items[i] = {'order' : i};
                        itemConfig = config.items[i];
                    }
                    itemConfig = config.items[i];
                }
                item = this.createItemRow(i, rowsCount, sectionOrder, configBlock, itemConfig, tableTBody, config);
                tableTBody.appendChild(item);
            }
            Event.observe(rowsSelect, 'change', this.changeItemCount.bind(this, tableTBody, rowsSelect, sectionOrder, configBlock, config));

            if (config.isDefault) {
                configRow.select('input').each(function(elm) {  elm.disabled = true;});
                itemsRow.select('input').each(function(elm) {   elm.className = ''; elm.disabled = true;});
                configRow.select('select').each(function(elm) {   elm.className = ''; elm.disabled = true;});
                itemsRow.select('select').each(function(elm) {  elm.disabled = true;});
            }
            if (!config.removable) {
                rowsSelect.disabled = true;
            }
            requiredInput.disabled = !config.removable;
        },
        createItemRow : function(itemOrder, itemsCount, sectionOrder, configBlock, config, table, fieldConfig) {
            var row = this.createElement('tr', 'item');
            var ceil = this.createElement('td');
            row.appendChild(ceil);
            var itemLabel = this.createElement('input', 'required-entry');
            if (config && config.label) {
                itemLabel.value = config.label;
            }
            ceil.appendChild(itemLabel);
            Event.observe(itemLabel, 'change', this.editItemData.bind(this, itemOrder, 'label', itemLabel, false));
            this.tempFieldConfig.items[config.order] = config;
            ceil = this.createElement('td');
            row.appendChild(ceil);
            var itemValue = this.createElement('input', 'required-entry validate-dbname');
            itemValue.name = Math.random();
            if (config && config.value) {
                itemValue.value = config.value;
            }
            ceil.appendChild(itemValue);
            Event.observe(itemValue, 'change', this.editItemData.bind(this, itemOrder, 'value', itemValue, false));
            ceil = this.createElement('td');
            row.appendChild(ceil);
            var itemChecked = this.createElement('select');
            itemChecked.addClassName('set-checked');
            ceil.appendChild(itemChecked);
            var checkedOption = null;
            for (var i = 0; i <= 1; i++) {
                checkedOption = this.createElement('option');
                checkedOption.value = i;
                checkedOption.update(i ? this.translates.yes : this.translates.no);
                itemChecked.appendChild(checkedOption);
                if (config && config.selected && i) {
                    checkedOption.selected = true;
                }
            }
            Event.observe(itemChecked, 'change', this.editItemData.bind(this, itemOrder, 'selected', itemChecked, true));
            ceil = this.createElement('td', 'sort');
            row.appendChild(ceil);
            if (!fieldConfig || !fieldConfig.isDefault) {
                if (itemOrder != itemsCount) {
                    var moveDown = this.createElement('div', 'sort-arrow-down');
                    ceil.appendChild(moveDown);
                    Event.observe(moveDown, 'click', this.moveItemRowDown.bind(this, itemOrder, itemsCount, sectionOrder, configBlock, config, table));
                }
                if (itemOrder != 1) {
                    var moveUp = this.createElement('div', 'sort-arrow-up');
                    ceil.appendChild(moveUp);
                    Event.observe(moveUp, 'click', this.moveItemRowUp.bind(this, itemOrder, itemsCount, sectionOrder, configBlock, config, table));
                }
            }
            ceil = this.createElement('td');
            row.appendChild(ceil);
            if (!fieldConfig || !fieldConfig.isDefault) {
                var removeLink = this.createElement('span', 'action-link');
                ceil.appendChild(removeLink);
                removeLink.update(this.translates.removeLCase);
                Event.observe(removeLink, 'click', this.removeItemRow.bind(this, itemOrder, sectionOrder, configBlock, table));
            }
            return row;
        },
        removeItemRow : function(itemOrder, sectionOrder, configBlock, table) {
            if (this.rowsSelect.value == 1) {
                alert(this.translates.onlyOneItem);
            } else if (confirm(this.translates.removeItem)) {
                this.rowsSelect.value -= 1;
                var tempItems = [];
                var rows = table.select('.item');
                for (var i = itemOrder - 1; i < rows.length; i++) {
                    rows[i].remove();
                    if (i != itemOrder - 1) {
                        this.tempFieldConfig.items[i + 1].order -= 1;
                        tempItems.push(this.tempFieldConfig.items[i + 1]);
                    }
                    delete this.tempFieldConfig.items[i + 1];
                }
                var item = null;
                for (i = 0; i < tempItems.length; i++) {
                    item = this.createItemRow(tempItems[i].order, this.rowsSelect.value, sectionOrder, configBlock, tempItems[i], table);
                    table.appendChild(item);
                }
                var newRows = table.select('.item');
                if (newRows.length == 2 || !tempItems.length) {
                    if (newRows[newRows.length - 1].select('.sort-arrow-down')[0]) {
                        newRows[newRows.length - 1].select('.sort-arrow-down')[0].remove();
                    }
                }
                if (newRows.length == 1) {
                    newRows[0].select('.sort')[0].update();
                }
                var minRequiredInput = this.popup.config.select('.min-required')[0];
                if (minRequiredInput) {
                    var countItems = this.countTempItems();
                    var minRequiredValue = parseInt(minRequiredInput.value);
                    if (minRequiredValue > countItems) {
                        minRequiredInput.value = countItems;
                    }
                }
            }
        },
        moveItemRowUp : function(itemOrder, itemsCount, sectionOrder, configBlock, config, table) {
            var currentItem = this.tempFieldConfig.items[itemOrder];
            currentItem.order = itemOrder - 1;
            var upperItem = this.tempFieldConfig.items[itemOrder - 1];
            upperItem.order = itemOrder;
            delete this.tempFieldConfig.items[itemOrder];
            delete this.tempFieldConfig.items[itemOrder - 1];
            var rows = table.select('tr');
            rows[itemOrder - 1].remove();
            rows[itemOrder].remove();
            var currentItemRow = this.createItemRow(itemOrder - 1, itemsCount, sectionOrder, configBlock, currentItem, table);
            if ((itemOrder - 1) == 1) {
                rows[0].insert({'after' : currentItemRow});
            } else {
                rows[itemOrder - 2].insert({'after' : currentItemRow});
            }
            currentItemRow.insert({'after' : this.createItemRow(itemOrder, itemsCount, sectionOrder, configBlock, upperItem, table)});
        },
        moveItemRowDown : function(itemOrder, itemsCount, sectionOrder, configBlock, config, table) {
            var currentItem = this.tempFieldConfig.items[itemOrder];
            currentItem.order = itemOrder + 1;
            var upperItem = this.tempFieldConfig.items[itemOrder + 1];
            upperItem.order = itemOrder;
            delete this.tempFieldConfig.items[itemOrder];
            delete this.tempFieldConfig.items[itemOrder + 1];
            var rows = table.select('tr');
            rows[itemOrder + 1].remove();
            rows[itemOrder].remove();
            var upperItemRow = this.createItemRow(itemOrder, itemsCount, sectionOrder, configBlock, upperItem, table);
            if ((itemOrder) == 1) {
                rows[0].insert({'after' : upperItemRow});
            } else {
                rows[itemOrder - 1].insert({'after' : upperItemRow});
            }
            upperItemRow.insert({'after' : this.createItemRow(itemOrder + 1, itemsCount, sectionOrder, configBlock, currentItem, table)});
        },
        changeItemCount : function(table, countElm, sectionOrder, configBlock, config) {
            var rowsExist = table.select('.item').length;
            if (countElm.value < rowsExist) {
                alert(this.translates.cannotChangeQuantity);
                countElm.value = rowsExist;
            } else {
                var item = null;
                for (var i = rowsExist + 1; i <= countElm.value; i++) {
                    item = this.createItemRow(i, countElm.value, sectionOrder, configBlock, {'order' : i}, table);
                    table.appendChild(item);
                }
                if (rowsExist) {
                    var moveDown = this.createElement('div', 'sort-arrow-down');
                    table.select('.item')[rowsExist - 1].select('.sort')[0].appendChild(moveDown);
                    Event.observe(moveDown, 'click', this.moveItemRowDown.bind(this, rowsExist - 1, countElm.value, sectionOrder, configBlock, config, table));
                }
            }
        },
        editItemData : function(itemOrder, key, valueElm, isInt) {
            var value = valueElm.value;
            if (isInt) {
                value = parseInt(value);
            }
            if (key == 'value' && !this.isUniqueItemValue(value, itemOrder)) {
                valueElm.value = '';
                alert(this.translates.valueUsed);
                return;
            }
            if (key == 'selected'
                && this.isOneCheckField()
                && value
                && this.hasCheckedItems()
            ) {
                valueElm.value = 0;
                alert(this.translates.onlyOneItemChecked);
                return;
            }
            this.tempFieldConfig['items'][itemOrder][key] = value;
        },
        isOneCheckField : function() {
            if (this.tempFieldConfig.type == this.fieldTypes.radio
                || this.tempFieldConfig.type == this.fieldTypes.select_box
                || this.tempFieldConfig.type == this.fieldTypes.list_box
            ) {
                return true;
            }
        },
        hasCheckedItems : function() {
            var selects = this.popup.config.select('.set-checked');
            var counts = 0;
            for (var i = 0; i < selects.length; i++) {
                if (selects[i] && parseInt(selects[i].value)) {
                    counts++;
                    if (counts > 1) {
                        return true;
                    }
                }
            }
            return false;
        },
        isUniqueItemValue : function(value, itemOrder) {
            for (var i = 0; i < this.tempFieldConfig['items'].length; i++) {
                if (this.tempFieldConfig['items'][i]) {
                    if (this.tempFieldConfig['items'][i]['value'] == value) {
                        return false;
                    }
                }
            }
            return true;
        },
        createInputBoxOptions : function(sectionOrder, configBlock, config) {
            this.createFieldTypesSelect(sectionOrder, configBlock, config);
            this.createLabelInput(sectionOrder, configBlock, config);
            this.createRequiredSelect(sectionOrder, configBlock, config);
            var rowConfig = {
                options : this.validationTypes
            };
            this.createSelectOptionsRow(sectionOrder, configBlock, config, rowConfig, 'validation');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'default_value');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'css_class');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'html_arg');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'name', 'required-entry validate-dbname', this.translates.noteNameDb);
        },
        createPasswordBoxOptions : function(sectionOrder, configBlock, config) {
            this.createFieldTypesSelect(sectionOrder, configBlock, config);
            this.createLabelInput(sectionOrder, configBlock, config);
            this.createRequiredSelect(sectionOrder, configBlock, config);
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'css_class');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'html_arg');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'name', 'required-entry validate-dbname', this.translates.noteNameDb);
        },
        createCheckboxOptions : function(sectionOrder, configBlock, config) {
            this.createFieldTypesSelect(sectionOrder, configBlock, config);
            this.createLabelInput(sectionOrder, configBlock, config);
            this.createRequiredSelect(sectionOrder, configBlock, config);
            this.createItemsOptions(sectionOrder, configBlock, config);
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'css_class');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'html_arg');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'name', 'required-entry validate-dbname', this.translates.noteNameDb);
        },
        createRadioOptions : function(sectionOrder, configBlock, config) {
            this.createFieldTypesSelect(sectionOrder, configBlock, config);
            this.createLabelInput(sectionOrder, configBlock, config);
            this.createItemsOptions(sectionOrder, configBlock, config);
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'css_class');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'html_arg');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'name', 'required-entry validate-dbname', this.translates.noteNameDb);
        },
        createSelectBoxOptions : function(sectionOrder, configBlock, config) {
            this.createFieldTypesSelect(sectionOrder, configBlock, config);
            this.createLabelInput(sectionOrder, configBlock, config);
            this.createItemsOptions(sectionOrder, configBlock, config);
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'css_class');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'html_arg');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'name', 'required-entry validate-dbname', this.translates.noteNameDb);
        },
        createListBoxOptions : function(sectionOrder, configBlock, config) {
            this.createFieldTypesSelect(sectionOrder, configBlock, config);
            this.createLabelInput(sectionOrder, configBlock, config);
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'size', 'required-entry validate-digits', null, 5);
            this.createItemsOptions(sectionOrder, configBlock, config);
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'css_class');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'html_arg');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'name', 'required-entry validate-dbname', this.translates.noteNameDb);
        },
        createMultiSelectBoxOptions : function(sectionOrder, configBlock, config) {
            this.createFieldTypesSelect(sectionOrder, configBlock, config);
            this.createLabelInput(sectionOrder, configBlock, config);
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'size', 'required-entry validate-digits', null, 5);
            this.createItemsOptions(sectionOrder, configBlock, config);
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'css_class');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'html_arg');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'name', 'required-entry validate-dbname', this.translates.noteNameDb);
        },
        createTextareaOptions : function(sectionOrder, configBlock, config) {
            this.createFieldTypesSelect(sectionOrder, configBlock, config);
            this.createLabelInput(sectionOrder, configBlock, config);
            this.createRequiredSelect(sectionOrder, configBlock, config);
            //this.createInputOptionsRow(sectionOrder, configBlock, config, 'cols', 'required-entry validate-max-digit-35', this.translates.noteMaxCols, 35);
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'rows', 'required-entry validate-digits', null, 5);
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'default_value');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'css_class');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'html_arg');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'name', 'required-entry validate-dbname', this.translates.noteNameDb);
        },
        createFileOptions : function(sectionOrder, configBlock, config) {
            this.createFieldTypesSelect(sectionOrder, configBlock, config);
            this.createLabelInput(sectionOrder, configBlock, config);
            this.createRequiredSelect(sectionOrder, configBlock, config);
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'file_extensions', null, this.translates.noteFileExt, 'png, jpg, jpeg, gif');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'max_file_size', 'validate-digits', this.translates.noteFileSize, '1048576');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'css_class');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'html_arg');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'name', 'required-entry validate-dbname', this.translates.noteNameDb);
        },
        createStaticTextOptions : function(sectionOrder, configBlock, config) {
            this.createFieldTypesSelect(sectionOrder, configBlock, config);
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'static_text', 'required-entry', null, null, true);
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'css_class');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'html_arg');
        },
        createCaptchOptions : function(sectionOrder, configBlock, config) {
            this.createFieldTypesSelect(sectionOrder, configBlock, config);
            this.createLabelInput(sectionOrder, configBlock, config);
            var rowConfig = {
                options : this.captchaTypes
            };
            this.createSelectOptionsRow(sectionOrder, configBlock, config, rowConfig, 'captcha');
        },
        createDateOptions : function(sectionOrder, configBlock, config) {
            this.createFieldTypesSelect(sectionOrder, configBlock, config);
            this.createLabelInput(sectionOrder, configBlock, config);
            this.createRequiredSelect(sectionOrder, configBlock, config);
            var rowConfig = {
                options : this.validationTypes
            };
            this.createSelectOptionsRow(sectionOrder, configBlock, config, rowConfig, 'validation');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'default_value');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'css_class');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'html_arg');
            this.createInputOptionsRow(sectionOrder, configBlock, config, 'name', 'required-entry validate-dbname', this.translates.noteNameDb);
        },
        createInputOptionsRow : function(sectionOrder, configBlock, config, optionKey, validateClass, note, defaultValue, isTextarea) {
            var row = this.createElement('div', 'row');
            configBlock.appendChild(row);
            var rowLabel = this.createElement('div', 'label');
            row.appendChild(rowLabel);
            rowLabel.update(this.translates[optionKey] + ':');
            var rowValue = this.createElement('div', 'value');
            row.appendChild(rowValue);
            var elementType = isTextarea ? 'textarea' : 'input';
            var rowValueInput = this.createElement(elementType);
            if (isTextarea) {
                rowValueInput.setStyle({width: '228px'});
                rowValueInput.rows = 10;
            }
            rowValueInput.name = Math.random();
            if (this.isIE7) {
                rowValueInput.addClassName('ie7-margin');
            }
            if (config && config[optionKey]) {
                rowValueInput.value = config[optionKey];
            } else if (defaultValue) {
                if (!this.sectionsHiddenInputs[sectionOrder]['fields'] || !this.sectionsHiddenInputs[sectionOrder]['fields'][config.order]) {
                    rowValueInput.value = defaultValue;
                    this.tempFieldConfig[optionKey] = defaultValue;
                }
            }
            if (config && (!config.removable || config.isDefault) && optionKey == 'name') {
                validateClass = 'required-entry';
                rowValueInput.disabled = true;
            }
            rowValue.appendChild(rowValueInput);
            if (optionKey == 'name') {
                Event.observe(rowValueInput, 'change', this.checkName.bind(this, rowValueInput));
            } else {
                Event.observe(rowValueInput, 'change', this.editFieldData.bind(this, optionKey, rowValueInput, false));
            }
            if (validateClass) {
                rowValueInput.addClassName(validateClass);
            }
            if (note) {
                var rowComment = this.createElement('p', 'note');
                rowComment.update(note);
                rowValue.appendChild(rowComment);
            }
        },
        checkName : function(inputElm) {
            var searchName = inputElm.value;
            for (var i = 0; i < this.sections.length; i++) {
                if (this.sections[i]) {
                    for (var j = 0; j < this.sections[i]['fields'].length; j++) {
                        if (this.sections[i]['fields'][j] && this.sections[i]['fields'][j].name
                            && this.sections[i]['fields'][j].name == searchName
                        ) {
                            alert(this.translates.nameUsed);
                            this.tempFieldConfig.name = '';
                            inputElm.value = '';
                            return;
                        }
                    }
                }
            }
            this.editFieldData('name', inputElm, false);
        },
        createSelectOptionsRow : function(sectionOrder, configBlock, config, rowConfig, optionKey) {
            var row = this.createElement('div', 'row');
            configBlock.appendChild(row);
            var rowLabel = this.createElement('div', 'label');
            row.appendChild(rowLabel);
            rowLabel.update(this.translates[optionKey] + ':');
            var rowValue = this.createElement('div', 'value');
            row.appendChild(rowValue);
            var rowValueSelect = this.createElement('select');
            rowValue.appendChild(rowValueSelect);
            var rowValueSelectOption = null;
            if (isNaN(rowConfig)) {
                for (var key in rowConfig.options) {
                    rowValueSelectOption = this.createElement('option');
                    rowValueSelectOption.value = rowConfig.options[key];
                    rowValueSelectOption.update(this.translates[key]);
                    rowValueSelect.appendChild(rowValueSelectOption);
                    if (config && config[optionKey] && config[optionKey] == rowConfig.options[key]) {
                        rowValueSelectOption.selected = true;
                    }
                }
            } else {
                for (var i = 1; i <= rowConfig; i++) {
                    rowValueSelectOption = this.createElement('option');
                    rowValueSelectOption.value = i;
                    rowValueSelectOption.update(i);
                    rowValueSelect.appendChild(rowValueSelectOption);
                    if (config && config[optionKey] && config[optionKey] == i) {
                        rowValueSelectOption.selected = true;
                    }
                }
            }
            this.tempFieldConfig[optionKey] = rowValueSelect.value;
            Event.observe(rowValueSelect, 'change', this.editFieldData.bind(this, optionKey, rowValueSelect, false));
            rowValueSelect.disabled = (config && !config.removable);
        },
        registerActiveField : function(sectionOrder, fieldOrder, fieldConfig, field, ceil, e) {
            if (this.doNotMoveField) {
                return;
            }
            this.activeSectionOrder = sectionOrder;
            this.activeFieldOrder = fieldOrder;
            this.activeCeil = ceil;
            this.tempFieldConfigMove = fieldConfig;
            this.activeField = field;
            var width = field.getWidth() + 'px';
            var height = field.getHeight() + 'px';
            this.activeField.absolutize();
            this.activeFieldOffset = this.activeField.positionedOffset();
            this.activeFieldTop = e.pageY - this.activeFieldOffset.top;
            this.activeFieldLeft = e.pageX - this.activeFieldOffset.left;
            this.activeField.setStyle({
                'zIndex' :1000,
                'backgroundColor' :'#d6d6d6',
                'width'  : width,
                'height' : height
            });
            this.calculateEmtyCeilOffset();
        },
        moveActiveField : function(e) {
            if (this.activeField) {
                this.activeField.setStyle({left: (e.pageX - this.activeFieldLeft) + 'px', top: (e.pageY - this.activeFieldTop) + 'px'});
            }
        },
        unregisterActiveField : function(e) {
            if (this.activeField) {
                var targetCeil = null;
                for (var i = 0; i < this.emptyCeils.length; i++) {
                    if (e.pageX > this.emptyCeils[i].x && e.pageX < this.emptyCeils[i].x + this.emptyCeils[i].width
                        && e.pageY > this.emptyCeils[i].y && e.pageY < this.emptyCeils[i].y + this.emptyCeils[i].height
                    ) {
                        targetCeil = this.emptyCeils[i];
                        break;
                    }
                }
                if (targetCeil) {
                    delete this.sections[this.activeSectionOrder]['fields'][this.activeFieldOrder];
                    this.tempFieldConfigMove.order = targetCeil.formOrder;
                    this.sections[targetCeil.sectionOrder]['fields'][targetCeil.formOrder] = this.tempFieldConfigMove;
                    if (!this.tempFieldConfigMove.removable) {
                        this.sections[targetCeil.sectionOrder].removable = false;
                    }
                    this.sectionsBlocks[targetCeil.sectionOrder].remove();
                    delete this.sectionsHiddenInputs[targetCeil.sectionOrder];
                    this.addSection(this.sections[targetCeil.sectionOrder], true);
                    this.sectionsBlocks[this.activeSectionOrder].remove();
                    delete this.sectionsHiddenInputs[this.activeSectionOrder];
                    this.addSection(this.sections[this.activeSectionOrder], true);
                    this.activeField.remove();
                } else {
                    new Effect.Morph(this.activeField, {
                        style: 'top:'+ this.activeFieldOffset.top +'px; left: '+ this.activeFieldOffset.left +'px;',
                        duration: 0.8
                    });
                    var ceil = this.activeCeil;
                    var field = this.activeField;
                    setTimeout(function() {
                        ceil.appendChild(field);
                        field.writeAttribute('style','');
                    }, 900);
                }
                this.activeSectionOrder = null;
                this.activeFieldOrder = null;
                this.activeField = null;
                this.activeCeil = null;
                this.tempFieldConfigMove = null;
            }
        },
        calculateEmtyCeilOffset : function() {
            var ceils = this.block.select('.empty');
            this.emptyCeils = [];
            for (var i = 0; i < ceils.length; i++) {
                this.emptyCeils.push({
                    'x'            : ceils[i].positionedOffset().left,
                    'y'            : ceils[i].positionedOffset().top,
                    'width'        : ceils[i].getWidth(),
                    'height'       : ceils[i].getHeight(),
                    'sectionOrder' : ceils[i].section,
                    'formOrder'    : ceils[i].form,
                    'ceil'         : ceils[i]
                });
                ceils[i].writeAttribute('style','');
            }
        },
        createField : function(field, sectionOrder, ceil) {
            var fieldBlock = null;
            switch (parseInt(field.type)) {
                case this.fieldTypes.input_box:
                    fieldBlock = this.createInputBox(field);
                    break;
                case this.fieldTypes.password_box:
                    fieldBlock = this.createPasswordBox(field);
                    break;
                case this.fieldTypes.checkbox:
                    fieldBlock = this.createCheckbox(field);
                    break;
                case this.fieldTypes.radio:
                    fieldBlock = this.createRadioBox(field);
                    break;
                case this.fieldTypes.select_box:
                    fieldBlock = this.createSelectBox(field);
                    break;
                case this.fieldTypes.list_box:
                    fieldBlock = this.createListBox(field);
                    break;
                case this.fieldTypes.multiselect_box:
                    fieldBlock = this.createMultiSelectBox(field);
                    break;
                case this.fieldTypes.textarea:
                    fieldBlock = this.createTextareaBox(field);
                    break;
                case this.fieldTypes.file:
                    fieldBlock = this.createFileBox(field);
                    break;
                case this.fieldTypes.static_text:
                    fieldBlock = this.createStaticTextBox(field);
                    break;
                case this.fieldTypes.captcha:
                    fieldBlock = this.createCaptchaBox(field);
                    break;
                case this.fieldTypes.date:
                    fieldBlock = this.createDateBox(field);
                    break;

                default: fieldBlock = this.createElement('div');
            }
            fieldBlock.addClassName('field-container');
            if (field) {
                fieldBlock.id = 'section_' + sectionOrder + '_field' + field.order;
                Event.observe(fieldBlock, 'mousedown', this.registerActiveField.bind(this, sectionOrder, field.order, field, fieldBlock, ceil));
            }
            var hiddenInput = null;
            for (var key in field) {
                if (field[key]) {
                    if (field[key] instanceof Array) {
                        // for field items
                        if (this.sectionsHiddenInputs[sectionOrder]['fields'] && this.sectionsHiddenInputs[sectionOrder]['fields'][field.order]) {
                            for (var i = 0; i < field[key].length; i++) {
                                if (field[key]) {
                                    for (var itemKey in field[key][i]) {
                                        if (field[key][i][itemKey]) {
                                            hiddenInput = this.createElement('input');
                                            hiddenInput.type = 'hidden';
                                            hiddenInput.name = 'sections[' + sectionOrder + '][fields][' + field.order + '][items]['+ field[key][i].order +'][' + itemKey + ']';
                                            hiddenInput.value = field[key][i][itemKey];
                                            if (!this.sectionsHiddenInputs[sectionOrder]['fields'][field.order]['items']) {
                                                this.sectionsHiddenInputs[sectionOrder]['fields'][field.order]['items'] = [];
                                            }
                                            if (!this.sectionsHiddenInputs[sectionOrder]['fields'][field.order]['items'][field[key][i].order]) {
                                                this.sectionsHiddenInputs[sectionOrder]['fields'][field.order]['items'][field[key][i].order] = [];
                                            }
                                            this.sectionsHiddenInputs[sectionOrder]['fields'][field.order]['items'][field[key][i].order][itemKey] = hiddenInput;
                                            this.sectionsBlocks[sectionOrder].appendChild(hiddenInput);
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        hiddenInput = this.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'sections[' + sectionOrder + '][fields][' + field.order + '][' + key + ']';
                        hiddenInput.value = field[key];
                        if (!this.sectionsHiddenInputs[sectionOrder]['fields']) {
                            this.sectionsHiddenInputs[sectionOrder]['fields'] = [];
                        }
                        if (!this.sectionsHiddenInputs[sectionOrder]['fields'][field.order]) {
                            this.sectionsHiddenInputs[sectionOrder]['fields'][field.order] = [];
                        }
                        this.sectionsBlocks[sectionOrder].appendChild(hiddenInput);
                        this.sectionsHiddenInputs[sectionOrder]['fields'][field.order][key] = hiddenInput;
                    }
                }
            }

            return fieldBlock;
        },
        createInputBox : function(field) {
            var fieldContainer = this.createFieldContainer(field);
            var input = this.createElement('input');
            input.type = 'text';
            input.readonly = true;
            input.value = field.default_value || '';
            fieldContainer.appendChild(input);
            return fieldContainer;
        },
        createPasswordBox : function(field) {
            var fieldContainer = this.createFieldContainer(field);
            var input = this.createElement('input');
            input.type = 'password';
            fieldContainer.appendChild(input);
            return fieldContainer;
        },
        createCheckbox : function(field) {
            var checkboxes = this.createFieldContainer(field);
            if (field.items) {
                field.items = this.orderItems(field.items);
                for (var i = 0; i < field.items.length; i++) {
                    if (field.items[i]) {
                        this.createCheckboxRow(field.items[i], checkboxes, 'checkbox');
                    }
                }
            }
            return checkboxes;
        },
        createRadioBox : function(field) {
            var checkboxes = this.createFieldContainer(field);
            var itemElm = null;
            if (field.items) {
                field.items = this.orderItems(field.items);
                for (var i = 0; i < field.items.length; i++) {
                    if (field.items[i]) {
                        itemElm = this.createCheckboxRow(field.items[i], checkboxes, 'radio');
                        itemElm.name = field.name;
                    }
                }
            }
            return checkboxes;
        },
        createSelectBox : function(field) {
            var fieldContainer = this.createFieldContainer(field);
            if (field.items) {
                field.items = this.orderItems(field.items);
                var selectContainer = this.createElement('select');
                fieldContainer.appendChild(selectContainer);
                var selectOption = null;
                for (var i = 0; i < field.items.length; i++) {
                    if (field.items[i]) {
                        selectOption = this.createElement('option');
                        selectContainer.appendChild(selectOption);
                        selectOption.update(field.items[i].label);
                        if (field.items[i].selected) {
                            selectOption.selected = true;
                        }
                    }
                }
            }
            return fieldContainer;
        },
        createListBox : function(field) {
            var fieldContainer = this.createFieldContainer(field);
            if (field.items) {
                field.items = this.orderItems(field.items);
                var selectContainer = this.createElement('select');
                selectContainer.size = field.size;
                fieldContainer.appendChild(selectContainer);
                var selectOption = null;
                for (var i = 0; i < field.items.length; i++) {
                    if (field.items[i]) {
                        selectOption = this.createElement('option');
                        selectContainer.appendChild(selectOption);
                        selectOption.update(field.items[i].label);
                        if (field.items[i].selected) {
                            selectOption.selected = true;
                        }
                    }
                }
            }
            return fieldContainer;
        },
        createMultiSelectBox : function(field) {
            var fieldContainer = this.createFieldContainer(field);
            if (field.items) {
                field.items = this.orderItems(field.items);
                var selectContainer = this.createElement('select');
                selectContainer.size = field.size;
                selectContainer.multiple = true;
                fieldContainer.appendChild(selectContainer);
                var selectOption = null;
                for (var i = 0; i < field.items.length; i++) {
                    if (field.items[i]) {
                        selectOption = this.createElement('option');
                        selectContainer.appendChild(selectOption);
                        selectOption.update(field.items[i].label);
                        if (field.items[i].selected) {
                            selectOption.selected = true;
                        }
                    }
                }
            }
            return fieldContainer;
        },
        createTextareaBox : function(field) {
            var fieldContainer = this.createFieldContainer(field);
            var textarea = this.createElement('textarea');
            textarea.readonly = true;
            textarea.rows = field.rows;
            if (field.cols) {
                textarea.cols = field.cols;
            }
            textarea.update(field.default_value || '');
            fieldContainer.appendChild(textarea);
            return fieldContainer;
        },
        createFileBox : function(field) {
            var fieldContainer = this.createFieldContainer(field);
            var input = this.createElement('input');
            input.type = 'file';
            fieldContainer.appendChild(input);
            var fieldComment = this.createElement('span', 'comment');
            var note = '';
            if (field.file_extensions) {
                note += '<br/>' + this.translates.file_extensions + ': ' + field.file_extensions;
            }
            if (field.max_file_size) {
                note += '<br/>' + this.translates.max_file_size + ': ' + field.max_file_size;
            }
            fieldComment.update(note);
            fieldContainer.appendChild(fieldComment);
            return fieldContainer;
        },
        createStaticTextBox : function(field) {
            var fieldContainer = this.createFieldContainer(field);
            fieldContainer.update(field.static_text);
            return fieldContainer;
        },
        createCaptchaBox : function(field) {
            var fieldContainer = this.createFieldContainer(field);
            var captchaType = '';
            switch (parseInt(field.captcha)) {
                case this.captchaTypes.alikon_mod:
                    captchaType = 'alikonmod';
                    break;
                case this.captchaTypes.captcha_form:
                    captchaType = 'captchaform';
                    break;
                case this.captchaTypes.secur_image:
                    captchaType = 'securimage';
                    break;
            }
            var img = this.createElement('div', captchaType);
            fieldContainer.appendChild(img);
            var input = this.createElement('input');
            input.type = 'text';
            var fieldComment = this.createElement('span', 'comment');
            var note = this.translates.captchaNote + '<br/>';
            fieldComment.update(note);
            fieldContainer.appendChild(fieldComment);
            fieldContainer.appendChild(input);
            return fieldContainer;
        },
        createDateBox : function(field) {
            var fieldContainer = this.createFieldContainer(field);
            var input = this.createElement('input');
            input.type = 'text';
            input.readonly = true;
            input.value = field.default_value || '';
            fieldContainer.appendChild(input);
            //var img = this.createElement('img');
            //img.src = this.skinUrl + 'adminhtml/default/default/images/grid-cal.gif';
            //fieldContainer.appendChild(img);
            return fieldContainer;
        },
        createFieldContainer : function(field) {
            var container = this.createElement('div');
            if (field.label) {
                var label = this.createElement('span');
                label.update(field.label + ':');
                container.appendChild(label);
                if (field.required || field.min_required) {
                    var required = this.createElement('span', 'required');
                    required.update('*');
                    container.appendChild(required);
                }
                container.appendChild(document.createElement('br'));
            }
            return container;
        },
        createCheckboxRow : function(option, parentBlock, type) {
            var checkbox = this.createElement('input');
            checkbox.type = type;
            if (option.selected) {
                checkbox.checked = true;
            }
            parentBlock.appendChild(checkbox);
            var label = this.createElement('span');
            label.update(option.label);
            parentBlock.appendChild(label);
            parentBlock.appendChild(this.createElement('br'));
            return checkbox;
        },
        orderItems : function(items) {
            var outputItems = [];
            for (var i = 0; i < items.length; i++) {
                if (items[i]) {
                    outputItems[items[i].order] = items[i];
                }
            }
            return outputItems;
        },
        /**
         * Create popup for editing of a field
         */
        createPopup : function() {
            var background = this.createElement('div', 'popup-background');
            Event.observe(background, 'click', this.hidePopup.bind(this));
            var popupWindow = this.createElement('div', 'popup-window');
            this.block.appendChild(background);
            this.block.appendChild(popupWindow);
            var configBox = this.createElement('div', 'config-box');
            popupWindow.appendChild(configBox);
            var popupLabel = this.createElement('div', 'label');
            popupLabel.update(this.translates.fieldConfig);
            var defaultFieldsDropdown = this.createElement('select', 'float-right');
            var defaultFieldsOptions = '<option value="0">' + this.translates.selectDefaultField + '</option>';
            for (var fieldName in this.defaultFields) {
                defaultFieldsOptions += '<option value="'+ fieldName +'">' + this.defaultFields[fieldName].label + '</option>';
            }
            defaultFieldsDropdown.update(defaultFieldsOptions);
            Event.observe(defaultFieldsDropdown, 'change', this.loadDefaultField.bind(this, defaultFieldsDropdown));
            popupLabel.appendChild(defaultFieldsDropdown);
            configBox.appendChild(popupLabel);
            var fieldConfig = this.createElement('div', 'config');
            configBox.appendChild(fieldConfig);
            var buttons = this.createElement('div', 'buttons');
            configBox.appendChild(buttons);
            this.popup = {
                'background' : background,
                'window'     : popupWindow,
                'config'     : fieldConfig,
                'buttons'    : buttons,
                'defaultFieldsDropdown' : defaultFieldsDropdown
            }
            this.hidePopup();
        },
        hidePopup : function(clearConfig) {
            this.tempFieldConfig = {};
            this.popup.config.update();
            this.popup.defaultFieldsDropdown.value = 0;
            this.popup.background.hide();
            this.popup.window.hide();
            this.doNotMoveField = false;
        },
        showPopup : function() {
            this.popup.background.show();
            this.popup.window.show();
            var windowHeight = document.viewport.getHeight();
            var top = ((windowHeight/100) * 15) + document.viewport.getScrollOffsets().top;
            this.popup.window.setStyle({'top' : top + 'px'});
        },
        createElement : function(elm, className) {
            var elm = document.createElement(elm);
            Element.extend(elm);
            if (className) {
                elm.addClassName(className);
            }
            return elm;
        }
    };

    Validation.add('validate-dbname', 'Please use only letters (a-z or A-Z) or numbers (0-9) or underscore(_) in this field. No spaces or other characters are allowed.', function (v) {
        return Validation.get('IsEmpty').test(v) ||  /^[a-zA-Z0-9_]+$/.test(v)
    });

    Validation.add('validate-max-digit-35', 'The value is not within the range: 1 - 35.', function (v) {
        var result = Validation.get('IsEmpty').test(v) ||  !/[^\d]/.test(v);
        var val = parseInt(v, 10);
        if (val <= 0 || val > 35) {
            result = false;
        }
        return result;
    });

    return Itoris;
});