/* WebForms 3.0.0 */
'use strict';
(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define([], factory);
    } else if (typeof module === 'object' && module.exports) {
        // Node. Does not work with strict CommonJS, but
        // only CommonJS-like environments that support module.exports,
        // like Node.
        module.exports = factory();
    } else {
        // Browser globals (root is window)
        root.mmLogic = factory();
    }
}(typeof self !== 'undefined' ? self : this, function () {
    function Logic(options) {
        const defaults = {
            targets: [],
            logicRules: [],
            fieldMap: {},
            uid: '',
            containerId: null,
            container: document,
            hiddenClass: 'mm-logic-hidden',
            isAdmin: false,
            processedTargets: [],
            actionHide: 'hide',
            actionShow: 'show',
        }

        // extends config
        this.options = Object.assign({}, defaults, options);

        return this.init();
    }

    Logic.prototype.initTargets = function () {
        let targets = this.options.targets;
        let addTargets = [];
        for (let i = 0; i < targets.length; i++) {
            if (!targets[i].id.includes('fieldset')) continue;
            addTargets.push({...targets[i], id: 'title_' + targets[i].id});
            for (let j = 0; j < this.options.logicRules.length; j++) {
                if (this.options.logicRules[j].target.includes(targets[i].id)) {
                    this.options.logicRules[j].target.push('title_' + targets[i].id);
                }
            }
        }
        this.options.targets = targets.concat(addTargets);
    }

    Logic.prototype.prepareLogicRules = function () {
        let logicRules = this.options.logicRules;
        for (let i = 0; i < logicRules.length; i++) {
            if (Array.isArray(logicRules[i]['value'])) {
                for (let j = 0; j < logicRules[i]['value'].length; j++) {
                    logicRules[i]['value'][j] = logicRules[i]['value'][j].toString();
                }
            }
        }
        this.options.logicRules = logicRules;
    }

    Logic.prototype.initLogicRules = function () {
        this.prepareLogicRules();
        for (let i = 0; i < this.options.logicRules.length; i++) {
            let rule = this.options.logicRules[i];
            if (typeof(rule) == 'object') {
                this.options.container.querySelectorAll(this.getFieldSelector(rule["field_id"])).forEach( (input,k) => {
                    let trigger_function = 'onchange';
                    if (typeof(input) != 'object') {
                        trigger_function = 'onclick';
                        if (input.type === 'select-multiple') {
                            trigger_function = 'onchange';
                        }
                    } else {
                        if (input.type === 'radio') {
                            trigger_function = 'onclick';
                        }
                    }
                    if (trigger_function === 'onchange') {
                        input.onchange = () => {
                            this.LogicEvent(input);
                        }
                        if (input.value) {
                            input.onchange();
                        }
                    } else {
                        input.onclick = () => {
                            this.LogicEvent(input);
                        }
                        if (input.value) {
                            input.onclick();
                        }
                    }
                });
            }
        }
    }

    Logic.prototype.init = function () {
        if (this.options.containerId) {
            const container = document.getElementById(this.options.containerId);
            if (container) {
                this.options.container = container;
            }
        }
        this.initTargets();
        this.initLogicRules();
        return this;
    }

    Logic.prototype.getFieldSelector = function (fieldId) {
        return this.options.isAdmin ? "[name^='" + this.options.uid + "[field][" + fieldId + "]']" :
            "[name^='field[" + fieldId + "]']";
    }

    Logic.prototype.LogicEvent = function (input) {
        this.options.processedTargets = [];
        for (let i = 0; i < this.options.targets.length; i++) {
            this.LogicTargetCheck(this.options.targets[i]);
        }
    }

    Logic.prototype.LogicTargetCheck = function (target) {
        if (typeof(target) != 'object') return false;
        for (let i = 0; i < this.options.logicRules.length; i++) {
            if (typeof(this.options.logicRules[i]) == 'object') {
                for (let j = 0; j < this.options.logicRules[i]['target'].length; j++) {
                    if (target["id"] === this.options.logicRules[i]['target'][j]) {
                        const FLAG = this.LogicRuleCheck(this.options.logicRules[i]);
                        const currentRule = this.options.logicRules[i];
                        let display = 'block';
                        if (FLAG) {
                            if (currentRule['action'] === this.options.actionHide) {
                                display = 'none';
                            }
                            this.options.processedTargets[target["id"]] = {
                                display: display,
                                flag: true
                            };
                        } else {
                            if (currentRule['action'] === this.options.actionShow) {
                                display = 'none';
                            }
                            if (this.options.processedTargets[target["id"]]) {
                                if (!this.options.processedTargets[target["id"]].flag) {
                                    this.options.processedTargets[target["id"]] = {
                                        display: display,
                                        flag: false
                                    };
                                }
                            } else {
                                this.options.processedTargets[target["id"]] = {
                                    display: display,
                                    flag: false
                                };
                            }
                        }
                        let jTargetId = this.options.isAdmin ? target["id"] + '_container' : target["id"];
                        let jTarget = this.options.container.querySelector('#' + jTargetId);
                        if (jTarget !== null) {
                            if (this.options.processedTargets[target["id"]].display === 'none') {
                                this.Hide(jTarget);
                            } else {
                                this.Show(jTarget);
                            }

                            if (this.options.isAdmin) {
                                if (this.options.processedTargets[target["id"]].display === 'none') {
                                    jTarget.querySelectorAll('.required-entry').forEach((el) => {
                                        el.setAttribute('disabled', 'disabled');
                                    });
                                } else {
                                    jTarget.querySelectorAll('.required-entry').forEach((el) => {
                                        el.removeAttribute('disabled');
                                    });
                                }
                            }
                        }

                        let jTargetRow = this.options.container.querySelector('#' + jTargetId + '_row');
                        if (jTargetRow !== null) {
                            this.options.processedTargets[target["id"]].display === 'none' ? this.Hide(jTargetRow) : this.Show(jTargetRow);
                        }

                        if (FLAG) {
                            for (let k = 0; k < this.options.logicRules.length; k++) {
                                if (typeof(this.options.logicRules[k]) == 'object' && this.options.logicRules[k] !== currentRule) {
                                    const nextRule = this.options.logicRules[k];
                                    if (typeof(target) == 'object') {
                                        const fieldsetId = this.options.isAdmin ? target["id"] : parseInt(target["id"].replace('fieldset_' + this.options.uid, ''));
                                        const targetId = this.options.isAdmin ? 'field_' + nextRule['field_id'] : 'field_' + this.options.uid + nextRule['field_id'];
                                        if (target["id"] === targetId || this.FieldInFieldset(nextRule['field_id'], fieldsetId)) {
                                            for (let n = 0; n < nextRule['target'].length; n++) {
                                                let visibility;
                                                if (nextRule['action'] === this.options.actionShow) visibility = 'hidden';
                                                if (nextRule['action'] === this.options.actionHide) visibility = 'visible';
                                                if (typeof(nextRule['target'][n]) == 'string') {
                                                    const newTarget = {
                                                        'id': nextRule['target'][n],
                                                        'logic_visibility': visibility
                                                    };
                                                    this.LogicTargetCheck(newTarget);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    Logic.prototype.LogicRuleCheck = function (logic) {
        let FLAG = false;
        let input = [];
        let field_type = 'select';
        let selected = 'selected';
        this.options.container.querySelectorAll(this.getFieldSelector(logic["field_id"])).forEach(element => {
            if (element.type === 'radio') {
                field_type = 'radio';
                selected = 'checked';
                input.push(element);
            } else if(element.type === 'checkbox') {
                field_type = 'checkbox';
                selected = 'checked';
                input.push(element);
            } else if (element.type === 'select-multiple') {
                let multiple = [];
                if (element.selectedOptions) {
                    for (let i = 0; i < element.selectedOptions.length; i++) {
                        multiple.push(element.selectedOptions[i].value);
                    }
                }
                input.push({'value': multiple, selected: true, input: element});
            } else {
                input.push({'value': element.value, selected: true, input: element});
            }
        });
        let value;
        if (logic['aggregation'] === 'any' || (logic['aggregation'] === 'all' && logic['logic_condition'] === 'notequal')) {
            if (logic['logic_condition'] === 'equal') {
                for (let i = 0; i < input.length; i++) {
                    if (typeof(input[i]) == 'object' && input[i]) {
                        if (input[i][selected]) {
                            for (let j = 0; j < logic['value'].length; j++) {
                                value = this.FieldIsLogicVisible(logic["field_id"]) ? input[i].value : false;
                                if (!Array.isArray(value)) value = [value];
                                if (value.includes(logic['value'][j])) FLAG = true;
                            }
                        }
                    }
                }
            } else {
                FLAG = true;
                let checked = false;
                for (let i = 0; i < logic['value'].length; i++) {
                    for (let j = 0; j < input.length; j++) {
                        if (typeof(input[j]) == 'object' && input[j])
                            if (input[j][selected]) {
                                checked = true;
                                value = this.FieldIsLogicVisible(logic["field_id"]) ? input[j].value : false;
                                if (!Array.isArray(value)) value = [value];
                                if (value.includes(logic['value'][i])) FLAG = false;
                            }
                    }
                }
                if (!checked) FLAG = false;
            }
        } else {
            FLAG = true;
            for (let i = 0; i < logic['value'].length; i++) {
                for (let j = 0; j < input.length; j++) {
                    if (typeof(input[j]) == 'object' && input[j]) {
                        value = this.FieldIsLogicVisible(logic["field_id"]) ? input[j].value : false;
                        if (!Array.isArray(value)) value = [value];
                        if (!input[j][selected] && value.includes(logic['value'][i])) FLAG = false;
                    }
                }
            }
        }
        return FLAG;
    }

    Logic.prototype.FieldIsVisible = function (fieldId) {
        let el = this.options.container.querySelectorAll('#field_' + this.options.uid + fieldId)[0];
        if (el !== undefined) {
            if (el.offsetWidth === 0 || el.offsetWidth === undefined) return false;
        } else {
            return false;
        }
        return true;
    }

    Logic.prototype.FieldIsLogicVisible = function (fieldId) {
        const el = this.options.isAdmin ? this.options.container.querySelector('#field_' + fieldId + '_container') :
            this.options.container.querySelector('#field_' + this.options.uid + fieldId);
        return !el.classList.contains(this.options.hiddenClass);
    }

    Logic.prototype.FieldInFieldset = function (fieldId, fieldsetId) {
        if (this.options.isAdmin) {
            if (typeof(fieldsetId) != 'string') return false;
            const fieldset = this.options.container.querySelector('#' + fieldsetId);
            if (!fieldset) {
                return false;
            }
            return !!fieldset.querySelector('#' + fieldId);
        }
        if (isNaN(fieldsetId)) return false;
        if (!this.options.fieldMap['fieldset_' + fieldsetId]) return false;
        return this.options.fieldMap['fieldset_' + fieldsetId].includes(fieldId);
    }

    Logic.prototype.Show = function (el) {
        el.classList.remove(this.options.hiddenClass);
        if (el.style) {
            if (el.style.display  === "none") {
                el.style.display = "";
            }
        }
    }

    Logic.prototype.Hide = function (el) {
        el.classList.add(this.options.hiddenClass);
    }

    return Logic;
}));