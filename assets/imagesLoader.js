(function ($) {
    var _prefixDelete = 'delete/';
    var _prefixAdd = 'add/';

    function getItemById(options, id)
    {
        var itemHtml;
        itemHtml = options.itemTemplate.replace('{id}', id).replace('{imageurl}', options.fnGetUrlImages(id));
        itemHtml = $(itemHtml);
        itemHtml.data('id', id).addClass('images-item');
        return itemHtml;
    }
    function getXmlHttp() {
        var xmlhttp;
        try {
            xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (E) {
                xmlhttp = false;
            }
        }
        if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
            xmlhttp = new XMLHttpRequest();
        }
        return xmlhttp;
    }


    function uploadFile(reader, file, url, item, $this) {

        var data = $this.data('imagesLoader');
        var options = data.options;

        var xhr = getXmlHttp();

        item.data('xhr', xhr);
        item.addClass('images-item-loading');
        item.removeClass('images-item-expected');

        if (options.fnProgressLoadItem)
        {
            xhr.upload.addEventListener("progress", function (e) {
                if (e.lengthComputable) {
                    var progress = (e.loaded * 100) / e.total;
                    options.fnProgressLoadItem(item, progress);
                }
            }, false);
        }

        xhr.onreadystatechange = function () {
            if (this.readyState == 4) {
                if (this.status == 200) {
                    /* ... все ок! смотрим в this.responseText ... */

                    item.removeData('xhr');
                    loadComplete(item, this.responseText, data);

                } else {
                    /* ... ошибка! ... */
                    item.removeData('xhr');
                    item.remove();
                    if (this.responseText)
                        alert(this.responseText);
                }
                startLoad.apply($this);
            }
        };

        xhr.open("POST", url, true);


        var boundary = "x1xw01w35x2x1504qwe";
        // Устанавливаем заголовки
        xhr.setRequestHeader("Content-Type", "multipart/form-data, boundary=" + boundary);
        xhr.setRequestHeader("Cache-Control", "no-cache");
        // Формируем тело запроса
        var body = "--" + boundary + "\r\n";
        body += "Content-Disposition: form-data; name='file'; filename='file'\r\n";
        body += "Content-Type: " + file.type + "\r\n\r\n";
        body += reader.result + "\r\n";
        body += "--" + boundary + "--";
        // Отправляем файлы.
        if (xhr.sendAsBinary) {
            // Только для Firefox
            xhr.sendAsBinary(body);
        } else {
            // Для остальных (как нужно по спецификации W3C)
            xhr.send(body);
        }
    }

    function loadComplete(item, id, data)
    {
        item.replaceWith(getItemById(data.options, id).data('new', true));
        data.imagesAdd.push(id);
    }


    function startLoad()
    {
        var data = this.data('imagesLoader');
        var options = data.options;

        var items = data.containImages.find('.images-item-expected');
        if (items.length)
        {
            var countLoad = data.containImages.find('.images-item-loading').length;

            if (countLoad < options.uploadCount)
            {
                if (!data.isload)
                {
                    this.trigger('startLoad');
                    data.isload = true;
                }
                var i = 0;
                while (i < items.length && (i + countLoad < options.uploadCount))
                {
                    var reader2 = new FileReader();
                    reader2.onload = function () {

                        uploadFile(reader2, items.eq(i).data('file'), options.url, items.eq(i), this);
                    };
                    reader2.readAsBinaryString(items.eq(i).data('file'));
                    i++;
                }


            }

        } else
        {
            if (data.isload)
            {
                this.trigger('endLoad');
                data.isload = false;
            }
        }
    }

    

    function addInput(containInput, id, type)
    {
        if (type === 'add')
        {
            containInput.append('<input type="hidden" value="' + (_prefixAdd + id) + '" class="images-input-add" >');

        } else
        {
            containInput.append('<input type="hidden" value="' + (_prefixDelete + id) + '" class="images-input-delete" >');
        }
    }

    var methods = {
        init: function (options) {

            options = $.extend({
                url: '',
                images: [],
                maxCount: null,
                fnGetUrlImages: function (id) {
                    return '/images-cache/' + id;
                },
                itemTemplate: '<div class="images-item" ><img src="{imageurl}"><a href="#" class="images-item-delete"></a></div>',
                maxSizeFile: 52428800, //50 мб
                uploadCount: 1 //одновременных загрузок

            }, options);

            return this.each(function () {
                var $this = $(this),
                        data = $this.data('imagesLoader'), containImages = $this.find('.images-items'), containInput = $this.find('.images-inputs');
                if (!data) {

                    if (!XMLHttpRequest.prototype.sendAsBinary) {
                        XMLHttpRequest.prototype.sendAsBinary = function (datastr) {
                            function byteValue(x) {
                                return x.charCodeAt(0) & 0xff;
                            }
                            var ords = Array.prototype.map.call(datastr, byteValue);
                            var ui8a = new Uint8Array(ords);
                            this.send(ui8a.buffer);
                        }
                    }

                    var i, imagesDelete = [], imagesOld = [], imagesAdd = [];

                    for (i = 0; i < options.images.length; i++)
                    {
                        if (options.images[i].substr(0, _prefixDelete.length) === _prefixDelete)
                        {
                            imagesDelete.push(options.images[i].substr(_prefixDelete.length + 1));

                        } else if (options.images[i].substr(0, _prefixAdd.length) === _prefixAdd)
                        {
                            imagesAdd.push(options.images[i].substr(_prefixAdd.length + 1));
                        } else
                        {
                            imagesOld.push(options.images[i]);
                        }
                    }

                    for (i = 0; i < imagesOld.length; i++)
                    {
                        containImages.append(getItemById(options, imagesOld[i]));
                    }

                    for (i = 0; i < imagesAdd.length; i++)
                    {
                        containImages.append(getItemById(options, imagesAdd[i]).data('new', true));
                        addInput(containInput, imagesAdd[i], 'add');
                    }

                    for (i = 0; i < imagesDelete.length; i++)
                    {
                        addInput(containInput, imagesDelete[i], 'delete');
                    }

                    containImages.on('click', '.images-item-delete', function () {
                        var bl = $(this).parents('.images-item');
                        methods.deleteItem.apply($this, [bl.data('id'), bl]);
                        return false;
                    });

                    $this.find('.images-input-file').change(function () {
                        var errors, imagesLoad = [];

                        if (options.maxCount && $this.imagesLoader('getCount') + this.files.length > parseInt(options.maxCount))
                        {
                            errors.push('Максимум можно файлов: ' + options.maxCount);
                        }

                        if (errors.length === 0)
                        {

                            $.each(this.files, function (i, file) {

                                if (options.maxSizeFile && file.size > options.maxSizeFile)
                                {
                                    errors.push('Превышен максимальный размер файла ' + file.name + ' ' + options.maxSizeFile + ' байт');
                                    return false;
                                }


                                var html = getItemById(options, 'loading').addClass('images-item-expected');
                                html.data('file', file);
                                imagesLoad.push(html);

                            });
                        }


                        if (errors.length)
                        {
                            alert(errors.join('\n'));
                        } else
                        {
                            if (imagesLoad.length)
                            {

                                for (var i = 0; i < imagesLoad.length; i++)
                                {
                                    containImages.append(imagesLoad[i]);
                                }
                                startLoad.apply($this);
                            }
                        }

                        this.value = null;

                    });

                    $this.data('imagesLoader', {
                        target: $this,
                        options: options,
                        imagesDelete: imagesDelete,
                        imagesAdd: imagesAdd,
                        imagesOld: imagesOld,
                        containInput: containInput,
                        containImages: containImages
                    });
                }
            });
        },
        deleteItem: function (id, item)
        {
            var data = this.data('imagesLoader'), i;

            if (item === undefined)
            {
                data.containImages.find('.images-item').each(function () {
                    if ($(this).data('id') === id)
                    {
                        item = $(this);
                        return false;
                    }
                });
            }



            if ((i = $.inArray(id, data.imagesOld)) !== -1)
            {
                addInput(data.containInput, id, 'delete');
                data.imagesDelete.push(id);
                data.imagesOld.splice(i, 1);
            } else
            if ((i = $.inArray(id, data.imagesAdd)) !== -1)
            {
                data.imagesAdd.splice(i, 1);
            }

            if (item)
            {
                if (item.data('xhr'))
                {
                    item.data('xhr').abort();
                }
                item.remove();
            }

        },
        getCount: function () {
            var data = this.data('imagesLoader');
            return data.imagesAdd.length + data.imagesOld.length;
        }
    };

    $.fn.imagesLoader = function (method) {

        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Метод с именем ' + method + ' не существует для jQuery.imagesLoader');
        }

    };
})(jQuery);