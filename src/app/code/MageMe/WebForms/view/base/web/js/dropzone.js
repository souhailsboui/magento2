define([
    'jquery',
    'mage/translate',
    'MageMe_WebForms/js/jquery.cookie',
    'mage/validation'], function ($, $t) {
    function JsWebFormsDropzone(options) {
        var o = {
            uid: '',
            url: '',
            removeUrl: '',
            fieldId: '',
            fieldName: '',
            dropZone: 1,
            dropZoneText: $t('Add file or drop here'),
            maxFiles: 1,
            allowedSize: 0,
            previewMaxWidth: '26px',
            previewMaxHeight: '26px',
            allowedExtensions: [],
            restrictedExtensions: [],
            validationCssClass: '',
            required: false,
            errorMsgDropzone: $t('This field has incorrect files'),
            errorMsgRequired: $t('This is a required field.'),
            errorMsgMaxFiles: $t('Maximum %s files in dropzone. Please remove upload or select some files to delete'),
            errorMsgAllowedExtensions: $t('Selected file has none of allowed extensions: %s'),
            errorMsgRestrictedExtensions: $t('Uploading of potentially dangerous files is not allowed.'),
            errorMsgAllowedSize: $t('Selected file exceeds allowed size: %s kB'),
            errorMsgUploading: $t('Error uploading file'),
            errorMsgNotReady: $t('Please wait... the upload is in progress.'),
            errorMsgRemoveFile: $t('Error removing file'),
            ui: false,
            containerId: null,
            container: document,
        };
        var cancelFile = function (event) {
            var parent = $(event.target).parents('.drop-zone-preview')[0];
            var formData = new FormData();
            if(window.FORM_KEY){
                formData.append('form_key', window.FORM_KEY);
            } else
            if ($.cookie('form_key')) {
                formData.append('form_key', $.cookie('form_key'));
            } else {
                formData.append('form_key', $('input[name="form_key"]')[0].value);
            }
            formData.append('hash', parent.dataset.hash);
            if (!o.dropZone) {
                uploadField().setAttribute('style', 'display:block');
                field.value = '';
                previewZone.removeChild(parent);
                return;
            }
            $.ajax({
                url: o.removeUrl,
                data: formData,
                type: 'post',
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (){
                    uploadField().setAttribute('style', 'display:block');
                    field.value = '';
                    for (var i = 0; i < dropZoneFiles.length; i++) {
                        if (parent.dataset.hash === dropZoneFiles[i]) {
                            dropZoneFiles.splice(i, 1);
                            inputHash.value = dropZoneFiles.join(';');
                        }
                    }
                    inputDropzone.value = '';
                    previewZone.removeChild(parent);
                },
                error: function (e){
                    alert(o.errorMsgRemoveFile);
                }
            });
        };
        for (var k in options) {
            if (options.hasOwnProperty(k)) o[k] = options[k];
        }
        if (!o.fieldId) return;
        if (o.containerId) {
            const container = document.getElementById(o.containerId);
            if (container) {
                o.container = container;
            }
        }
        var field = o.container.querySelector('#' + o.fieldId);
        var previewZone = o.container.querySelector('#' + o.fieldId + '_preview');
        if (!previewZone) {
            previewZone = document.createElement('div');
            previewZone.setAttribute('id', o.fieldId + '_preview');
            field.parentNode.appendChild(previewZone);
        }
        previewZone.querySelectorAll('.drop-zone-preview').forEach((preview) => {
            var close = preview.querySelector('.drop-zone-preview-icon-close');
            if (close) {
                close.addEventListener('click', cancelFile);
            }
        });
        var parentNode = field.parentNode;

        var fileCnt = 0;

        var fieldName = o.fieldName ? o.fieldName : 'hash' + field.name;

        var inputHash = o.container.querySelector('#' + o.fieldId + '_hash');
        if (!inputHash) {
            inputHash = document.createElement('input');
            inputHash.setAttribute('id', o.fieldId + '_hash');
            inputHash.setAttribute('type', 'hidden');
            inputHash.setAttribute('name', fieldName);
            inputHash.setAttribute('class', o.validationCssClass);
        }

        var inputDropzone = o.container.querySelector('#' + o.fieldId + '_dropzone');
        if (!inputDropzone) {
            inputDropzone = document.createElement('input');
            inputDropzone.setAttribute('id', o.fieldId + '_dropzone');
            inputDropzone.setAttribute('name', o.fieldName + '_dropzone');
            inputDropzone.setAttribute('type', 'file');
            inputDropzone.setAttribute('style', 'display:none');
            inputDropzone.setAttribute('multiple', 'multiple');
            inputDropzone.classList.add('validate-hidden');
        }
        var dataValidate = "{";
        dataValidate = dataValidate + "'validate-dropzone':true";
        if (o.required) {
            dataValidate = dataValidate + ", 'validate-dropzone-required':true";
        }
        if (o.maxFiles) {
            dataValidate = dataValidate + ", 'validate-dropzone-max-files':true";
        }
        dataValidate = dataValidate + "}";
        inputDropzone.setAttribute('data-validate', dataValidate);
        inputDropzone.setAttribute('data-msg-validate-dropzone', o.errorMsgDropzone);
        if (o.required) {
            inputDropzone.setAttribute('data-msg-validate-dropzone-required', o.errorMsgRequired);
        }
        if (o.maxFiles) {
            inputDropzone.setAttribute('data-msg-validate-dropzone-max-files', o.errorMsgMaxFiles);
            inputDropzone.setAttribute('data-max-files', o.maxFiles);
        }
        inputDropzone.setAttribute('data-uid', o.uid);

        if (o.dropZone) {
            o.removeUrl = o.url.replace('dropzoneUpload', 'dropzoneRemove');

            var dropZone = o.container.querySelector('#' + o.fieldId + '_dropzone_ui');
            if (!dropZone) {
                dropZone = document.createElement('div');
                dropZone.setAttribute('id', o.fieldId + '_dropzone_ui');
                dropZone.setAttribute('class', 'drop-zone');

                var dropZoneText = document.createElement('div');
                dropZoneText.setAttribute('class', 'dropzone-text');
                dropZone.appendChild(dropZoneText);

                var dropZoneIconPaperclip = document.createElement('span');
                dropZoneIconPaperclip.setAttribute('class', 'icon-paperclip');
                dropZoneText.appendChild(dropZoneIconPaperclip);

                var dropZoneLabel = document.createElement('div');
                dropZoneLabel.setAttribute('class', 'drop-zone-label');
                dropZoneLabel.innerHTML = o.dropZoneText;
                dropZoneText.appendChild(dropZoneLabel);
            }

            dropZone.addEventListener('click', function () {
                inputDropzone.click();
            });

            var handleFileDrop = function (evt) {
                evt.stopPropagation();
                evt.preventDefault();

                var files = evt.dataTransfer.files;

                fileCnt = dropZoneFiles.length;

                for (var i = 0, f; f = files[i]; i++) {
                    processFile(files[i]);
                }
            };

            var handleDragOver = function (evt) {
                evt.stopPropagation();
                evt.preventDefault();
                evt.dataTransfer.dropEffect = 'copy';
            };

            dropZone.addEventListener('dragover', handleDragOver, false);
            dropZone.addEventListener('drop', handleFileDrop, false);

            field.setAttribute('style', 'display:none');
            parentNode.appendChild(dropZone);

            parentNode.appendChild(inputHash);
            parentNode.appendChild(inputDropzone);
        } else {
            o.maxFiles = 1;
        }

        var uploadField = function () {
            if (o.dropZone) return dropZone;
            return field;
        };

        var dropZoneFiles = [];

        var processFile = function (file) {
            if (fileCnt >= o.maxFiles) {
                return;
            }

            fileCnt++;

            var preview = document.createElement('div');
            var hash = "";
            previewZone.appendChild(preview);
            preview.setAttribute('class', 'drop-zone-preview');
            preview.setAttribute('style', 'display:none');
            preview.setAttribute('style', 'display:block');
            preview.innerHTML = "";
            var errors = [];
            var fileName = file.name;
            var fileExt = fileName.substr(fileName.lastIndexOf('.') + 1).toLowerCase();
            var fileSize = file.size;
            var fileType = file.type;
            var fileSizeKB = (fileSize / 1024).toFixed(2);

            if (o.allowedExtensions.indexOf(fileExt) < 0 && o.allowedExtensions.length) {
                errors.push(o.errorMsgAllowedExtensions.replace('%s', o.allowedExtensions.join()));
            }
            if (o.restrictedExtensions.indexOf(fileExt) >= 0 && o.restrictedExtensions.length) {
                errors.push(o.errorMsgRestrictedExtensions);
            }
            if (fileSizeKB > o.allowedSize && o.allowedSize > 0) {
                errors.push(o.errorMsgAllowedSize.replace('%s', o.allowedSize.toString()));
            }

            if (errors.length && !o.dropZone) {
                field.value = '';
                preview.setAttribute('style', 'display:none');
                alert(errors.join("\n\n"));
            } else {
                var divAttachementContainer = document.createElement('div');
                divAttachementContainer.setAttribute('class', 'drop-zone-attachement-container');
                preview.appendChild(divAttachementContainer);

                var divAttachement = document.createElement('div');
                divAttachement.setAttribute('class', 'drop-zone-attachment');

                var spanIconFile = document.createElement('span');
                spanIconFile.setAttribute('class', 'drop-zone-preview-icon-file');
                divAttachement.appendChild(spanIconFile);

                var divPreviewFile = document.createElement('div');
                divPreviewFile.setAttribute('class', 'drop-zone-preview-file');
                divAttachement.appendChild(divPreviewFile);

                var divPreviewFilename = document.createElement('div');
                divPreviewFilename.setAttribute('class', 'drop-zone-preview-filename');
                divPreviewFilename.innerHTML = fileName.substr(0, fileName.length - 7);
                divPreviewFile.appendChild(divPreviewFilename);

                var divPreviewFilenameEnd = document.createElement('div');
                divPreviewFilenameEnd.setAttribute('class', 'drop-zone-preview-filename-end');
                divPreviewFilenameEnd.innerHTML = fileName.substr(-7);
                divPreviewFile.appendChild(divPreviewFilenameEnd);

                var divPreviewInfo = document.createElement('div');
                divPreviewInfo.setAttribute('class', 'drop-zone-preview-size');
                divPreviewFile.appendChild(divPreviewInfo);

                var spanIconClose = document.createElement('span');
                spanIconClose.setAttribute('class', 'drop-zone-preview-icon-close');
                spanIconClose.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="7" viewBox="0 0 10 10">\n' +
                    '<path d="M6.4,5l3.3-3.3c0.4-0.4,0.4-1,0-1.4s-1-0.4-1.4,0L5,3.6L1.7,0.3c-0.4-0.4-1-0.4-1.4,0s-0.4,1,0,1.4L3.6,5L0.3,8.3 c-0.4,0.4-0.4,1,0,1.4C0.5,9.9,0.7,10,1,10s0.5-0.1,0.7-0.3L5,6.4l3.3,3.3C8.5,9.9,8.7,10,9,10s0.5-0.1,0.7-0.3 c0.4-0.4,0.4-1,0-1.4L6.4,5z"></path>\n' +
                    '</svg>';
                spanIconClose.addEventListener('click', cancelFile);
                divAttachement.appendChild(spanIconClose);

                var divProgress = document.createElement('div');
                divProgress.setAttribute('class', 'drop-zone-progress');
                divProgress.setAttribute('style', 'width:0%');
                preview.appendChild(divProgress);

                divAttachementContainer.appendChild(divAttachement);

                var validImageTypes = ["image/gif", "image/jpeg", "image/png"];
                if (validImageTypes.indexOf(fileType) >= 0) {
                    var img = document.createElement('img');
                    img.setAttribute('style', 'width: 100%; max-width: ' + o.previewMaxWidth + '; max-height: ' + o.previewMaxHeight);

                    spanIconFile.appendChild(img);
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        img.setAttribute('src', e.target.result);
                    };

                    reader.readAsDataURL(file);
                } else {
                    spanIconFile.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 20 26">\n' +
                        '<path fill="currentColor" d="M13.41 0H2a2 2 0 0 0-2 2v22a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6.58L13.41 0zM15 7a2 2 0 0 1-2-2V1l6 6h-4z"></path>\n' +
                        '</svg>';
                }

                if (o.dropZone) {
                    var divReadyState = document.createElement('div');

                    var inputHiddenReady = document.createElement('input');
                    var inputHiddenReadyId = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                    inputHiddenReady.setAttribute('id', inputHiddenReadyId);
                    inputHiddenReady.setAttribute('type', 'hidden');
                    inputHiddenReady.setAttribute('class', 'required-entry');
                    divReadyState.appendChild(inputHiddenReady);

                    var divReadyError = document.createElement('div');
                    divReadyError.setAttribute('style', 'display:none');
                    divReadyError.setAttribute('class', 'validation-advice');
                    divReadyError.setAttribute('id', 'advice-required-entry-' + inputHiddenReadyId);
                    divReadyError.innerHTML = o.errorMsgNotReady;
                    divReadyState.appendChild(divReadyError);

                    divPreviewFile.appendChild(divReadyState);

                    if (errors.length) {
                        divPreviewInfo.setAttribute('class', 'drop-zone-error');
                        divPreviewInfo.innerHTML = errors.join('<br>');
                        return;
                    }
                    var uploadProgress = function (event) {
                        var percent = parseInt(event.loaded / event.total * 99);
                        divProgress.setAttribute('style', 'width:' + percent + '%');
                        divPreviewInfo.innerHTML = percent + '%';
                    };

                    var formData = new FormData();
                    if(window.FORM_KEY){
                        formData.append('form_key', window.FORM_KEY);
                    } else
                    if ($.cookie('form_key')) {
                        formData.append('form_key', $.cookie('form_key'));
                    } else {
                        formData.append('form_key', $('input[name="form_key"]')[0].value);
                    }
                    formData.append('file_id', field.getAttribute('name'));
                    formData.append(field.getAttribute('name'), file);

                    $.ajax({
                        url: o.url,
                        data: formData,
                        type: 'post',
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function (result){
                            inputDropzone.value = '';
                            divProgress.setAttribute('class', 'drop-zone-progress-success');
                            divPreviewInfo.innerHTML = fileSizeKB + 'KB';
                            var error = result.error.join('<br>');
                            hash = result.hash;

                            divPreviewFile.removeChild(divReadyState);

                            if (hash) {
                                dropZoneFiles.push(hash);
                                inputHash.value = dropZoneFiles.join(';');
                                if (o.ui) o.ui(inputHash.value);
                                preview.dataset.hash = hash;
                            }
                            if (error) {
                                divPreviewInfo.setAttribute('class', 'drop-zone-error');
                                divPreviewInfo.innerHTML = error;
                            }
                        },
                        error: function (e){
                            alert(o.errorMsgUploading);
                        },
                        xhr: function (){
                            var xhr = new XMLHttpRequest();
                            xhr.upload.addEventListener('progress', uploadProgress, false);
                            // xhr.onreadystatechange = stateChange;
                            return xhr;
                        }
                    });
                } else {
                    divPreviewInfo.innerHTML = fileSizeKB + 'KB';
                }
                if (fileCnt >= o.maxFiles) {
                    uploadField().setAttribute('style', 'display:none');
                }
            }

        };

        var handleFileSelect = function (evt) {
            var files = evt.target.files;
            fileCnt = dropZoneFiles.length;
            for (var i = 0, f; f = files[i]; i++) {
                processFile(files[i]);
            }
        };

        this.reset = function () {
            if (o.dropZone) {
                inputHash.value = '';
                fileCnt = 0;
                dropZoneFiles = [];
                dropZone.style.display = 'block';
                while (previewZone.firstChild) {
                    previewZone.removeChild(previewZone.firstChild);
                }
            } else {
                fileCnt = 0;
                uploadField().value = "";
                uploadField().setAttribute('style', 'display:block');
                while (previewZone.firstChild) {
                    previewZone.removeChild(previewZone.firstChild);
                }
            }
        };

        inputDropzone.addEventListener('change', handleFileSelect);
        field.addEventListener('change', handleFileSelect);
    }

    return JsWebFormsDropzone;
});
