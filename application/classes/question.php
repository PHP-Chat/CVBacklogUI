<?php

/**
 * -
 *
 * @oauthor  Kyra D. <kyra@existing.me>
 * @method   void __construct()
 * @todo     -
 * @uses     -
 */
class QuestionItem
{
    public function __construct(stdClass $questionData) {
        $this->questionData = $questionData;
    }

    /**
     * @return   string
     */
    public function getCloseReasonName() {
        return [
            'dupe' => 'Duplicate',
            'ot'   => 'Off-Topic',
            'pob'  => 'Primarily Opinion Based',
            'tb'   => 'Too Broad',
            'uwya' => 'Unclear What You&#39;re Asking',
        ][$this->getCloseReasonAcronymn(];
    }

    /**
     * @return   string
     */
    public function getCloseReasonAcronymn() {
        switch ($this->questionData->close_reason) {
            case 'duplicate':
            case 'exact duplicate':
                return 'dupe';
                break;
            case 'off topic':
            case 'off-topic':
            case 'too localized':
                return 'ot';
                break;
            case 'not constructive':
            case 'primarily opinion-based':
                return 'pob';
                break;
            case 'too broad':
                return 'tb';
                break;
            case 'not a real question':
            case 'unclear what you&#39;re asking':
                return 'uwya';
                break;
        }
    }

    /**
     * @return   string
     */
    public function getQuestionType() {
        if ($this->isAutoDeleteQuestion()) {
            return 'adelv';
        } else if ($this->isCloseQuestion()) {
            return'cv';
        } else if ($this->isDeleteQuestion()) {
            return 'delv';
        } else if ($this->isReviewQuestion()) {
            return 'rv';
        } else if ($this->isReopenQuestion()) {
            return 'ro';
        }
        return 'none';
    }

    /**
     * @return   bool
     */
    public function isDeleteQuestion() {
        return (isset($this->questionData->closed_date)
            && 172800 < (time() - $this->questionData->closed_date)
            && 0 === $this->questionData->reopen_vote_count);
    }

    /**
     * @return   bool
     */
    public function isReopenQuestion() {
        return (isset($this->questionData->closed_date)
            && 0 < $this->questionData->reopen_vote_count);
    }

    /**
     * @return   bool
     */
    public function isAutoDeleteQuestion() {

        $questionAge = (time() - $this->questionData->creation_date);

        if (isset($this->questionData->migrated_to)) {
            return true;
        } else if (2592000 < $questionAge
            && 0 > $this->questionData->score
            && !isset($this->questionData->answers)
            && !isset($this->questionData->locked_date)) {
            return true;
        } else if (31536000 < $questionAge
            && 0 === $this->questionData->score
            && !isset($this->questionData->answers)
            && !isset($this->questionData->locked_date)
            && !isset($this->questionData->comments[1])
            && $this->questionData->view_count <= (($questionAge / 86400) * 1.5)) {
            return true;
        } else if (!$this->questionData->is_answered
            && 0 === $this->questionData->reopen_vote_count
            && 0 >= $this->questionData->score
            && (isset($this->questionData->close_reason) && 'dupe' !== $this->getCloseReasonAcronymn())
            && !isset($this->questionData->locked_date)
            && (isset($this->questionData->closed_date) && 777600 < (time() - $this->questionData->closed_date))
            && (isset($this->questionData->last_edit_date) && 777600 > (time() - $this->questionData->last_edit_date))) {

            if (isset($this->questionData->answers)) {
                foreach ($this->questionData->answers as $answer) {
                    if (0 > $answer->score) {
                        return false;
                    }
                }
                return true;
            }
        }
        return false;
    }

    /**
     * @return   bool
     */
    public function isCloseQuestion() {
        return (!isset($this->questionData->closed_date)
            && 0 < $this->questionData->close_vote_count);
    }

    /**
     * @return   bool
     */
    public function isReviewQuestion() {
        return (!isset($this->questionData->closed_date)
            && 0 === $this->questionData->close_vote_count
            && 0 === $this->questionData->delete_vote_count
            && 0 === $this->questionData->reopen_vote_count
            && 1 < $this->questionData->down_vote_count);
    }

}
