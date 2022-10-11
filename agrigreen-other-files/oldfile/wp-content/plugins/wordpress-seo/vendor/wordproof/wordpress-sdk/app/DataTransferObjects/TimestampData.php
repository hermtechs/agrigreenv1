<?php

namespace WordProof\SDK\DataTransferObjects;

class TimestampData
{
    public static function fromPost($post)
    {
        return [
            'uid'           => $post->ID,
            'date_modified' => get_post_modified_time('c', false, $post->ID),
            'title'         => $post->post_title,
            'url'           => get_permalink($post),
            'content'       => $post->post_content,
        ];
    }
}
