<?php

namespace Biztech\Ausposteparcel\Model\System\Config;

use \Magento\Config\Model\Config\CommentInterface;

class Comment implements CommentInterface {

    public function getCommentText($elementValue) {
        return $html = __('To get the activation key, you can contact us at <a href="https://www.appjetty.com/support.htm" target="-">appjetty</a>');
    }
}
