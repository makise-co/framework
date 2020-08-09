<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Console\Commands;

class MakiseCommand extends AbstractCommand
{
    public function configure(): void
    {
        $this->setName('inspire');
        $this->setDescription('Makise\'s inspiring phrases');
    }

    public function handle(): void
    {
        $count = \count(self::PHRASES);
        $rand = \random_int(0, $count - 1);

        $phrase = self::PHRASES[$rand];

        $this->info($phrase);
    }

    protected const PHRASES = [
        'Every brilliant day should be lived for those who passed away.',
        'Everyone is watching someone other than themselves, someone important to them...',
        'People\'s feelings are memories that transcend time.',
        'I am a scientist, I have to act on my own theory. I can\'t let my emotions get in the way. But it\'s impossible to forget everything... because I\'ve known you for longer than we\'ve lived. This is reality. This is the world.',
        'You know, Okabe, whether time is slow or fast, depends on perception. Theory of relativity is so romantic.',
        'Maybe there are copies of me on countless world lines. Maybe all their minds are connected, forming a single “me.” That sounds wonderful, don’t you think? Being in all times and in all places.',
    ];
}
