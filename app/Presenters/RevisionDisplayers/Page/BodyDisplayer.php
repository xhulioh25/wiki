<?php

/*
 * This file is part of Bootstrap CMS.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Presenters\RevisionDisplayers\Page;

/**
 * This is the body displayer class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class BodyDisplayer extends AbstractDisplayer
{
    /**
     * Get the change description from the context of
     * the change being made by the current user.
     *
     * @return string
     */
    protected function current()
    {
        $title = trans('navigation.'.$this->name());
        return 'You updated the content of '.$title;
    }

    /**
     * Get the change description from the context of
     * the change not being made by the current user.
     *
     * @return string
     */
    protected function external()
    {
        return 'This user updated the content of'.$this->name();
    }
}
