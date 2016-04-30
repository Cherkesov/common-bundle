/**
 * Created by GoForBroke on 30.04.2016.
 */

$(function () {
    /**
     * @param {Image} image
     * @returns {boolean}
     */
    function isLandscape(image) {
        return image.width >= image.height;
    }

    $('body').on('change', 'input[type="file"][data-preview-canvas]', function (e) {
        var $fileInput = $(this),
            $canvas = $('#' + $fileInput.data('preview-canvas')),
            context = $canvas[0].getContext('2d');

        if (!$fileInput[0].files || !$fileInput[0].files[0]) {
            return false;
        }

        var reader = new FileReader();
        reader.onload = function (e) {
            var img = new Image();
            img.onload = function () {

                var previewWidth = $canvas.attr('width'),
                    previewHeight = $canvas.attr('height');

                if (!!$canvas.data('resizer')) {

                    var mt = 0,
                        ml = 0,
                        proportion = 1;
                    if (previewWidth > previewHeight) {
                        if (previewWidth > 0 && previewHeight > 0) {
                            proportion = isLandscape(img) ?
                            previewHeight / img.height :
                            previewWidth / img.width;
                        } else if (previewWidth > 0) {
                            proportion = previewWidth / img.width;
                        } else if (previewHeight > 0) {
                            proportion = previewHeight / img.height;
                        }
                    } else {
                        if (previewWidth > 0 && previewHeight > 0) {
                            proportion = isLandscape(img) ?
                            previewWidth / img.width :
                            previewHeight / img.height;
                        } else if (previewHeight > 0) {
                            proportion = previewHeight / img.height;
                        }
                        else if (previewWidth > 0) {
                            proportion = previewWidth / img.width;
                        }
                    }

                    img.width *= proportion;
                    img.height *= proportion;

                    if (previewWidth > 0 && previewHeight > 0) {
                        ml = -(img.width - previewWidth) / 2;
                        mt = -(img.height - previewHeight) / 2;
                    }

                    $canvas.width((previewWidth > 0) ? previewWidth : img.width);
                    $canvas.height((previewHeight > 0) ? previewHeight : img.height);

                    context.drawImage(img, ml, mt, img.width, img.height);
                } else {
                    if (previewWidth > 0)
                        img.width = previewWidth;
                    if (previewHeight > 0)
                        img.height = previewHeight;

                    $canvas.width(img.width);
                    $canvas.height(img.height);

                    context.drawImage(img, 0, 0, img.width, img.height);
                }
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
    });
});