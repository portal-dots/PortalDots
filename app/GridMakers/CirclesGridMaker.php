<?php

declare(strict_types=1);

namespace App\GridMakers;

use App\Eloquents\Circle;
use App\Eloquents\CustomForm;
use App\Eloquents\Form;
use App\Eloquents\Question;
use App\Eloquents\Tag;
use Illuminate\Database\Eloquent\Builder;
use App\GridMakers\Concerns\UseEloquent;
use App\GridMakers\Filter\FilterableKey;
use App\GridMakers\Filter\FilterableKeysDict;
use Illuminate\Database\Eloquent\Model;
use App\Services\Utils\FormatTextService;

class CirclesGridMaker implements GridMakable
{
    use UseEloquent;

    /**
     * @var FormatTextService
     */
    private $formatTextService;

    /**
     * @var Form
     */
    private $custom_form;

    public const CUSTOM_FORM_QUESTIONS_KEY_PREFIX = 'custom_form_question_';

    public function __construct(FormatTextService $formatTextService)
    {
        $this->formatTextService = $formatTextService;
        $this->custom_form = CustomForm::getFormByType('circle');
    }

    /**
     * @inheritDoc
     */
    protected function baseEloquentQuery(): Builder
    {
        return Circle::submitted()->select([
            'id',
            'name',
            'name_yomi',
            'group_name',
            'group_name_yomi',
            'submitted_at',
            'status',
            'status_set_at',
            'status_set_by',
            'notes',
            'created_at',
            'updated_at',
        ])->with(['tags', 'statusSetBy', 'answers' => function ($query) {
            if (isset($this->custom_form)) {
                $query->with('details.question')->where('form_id', $this->custom_form->id);
            }
        }]);
    }

    /**
     * @inheritDoc
     */
    public function keys(): array
    {
        // 現状 PortalDots は PHP7.3 以降をサポートすることにしているため、
        // PHP 7.4 からサポートされるスプレッド演算子を使わず、array_merge を使っている

        $before_custom_form_keys = [
            'id',
            'name',
            'name_yomi',
            'group_name',
            'group_name_yomi',
            'tags',
        ];

        $custom_form_keys = isset($this->custom_form) ?
            $this->custom_form->questions->map(function (Question $question) {
                return self::CUSTOM_FORM_QUESTIONS_KEY_PREFIX . $question->id;
            })->all() : [];

        $after_custom_form_keys = [
            'submitted_at',
            'status',
            'status_set_at',
            'status_set_by',
            'notes',
            'created_at',
            'updated_at',
        ];

        return array_merge($before_custom_form_keys, $custom_form_keys, $after_custom_form_keys);
    }

    /**
     * @inheritDoc
     */
    public function filterableKeys(): FilterableKeysDict
    {
        static $tags_choices = null;

        if (empty($tags_choices)) {
            $tags_choices = Tag::all()->toArray();
        }

        $users_type = FilterableKey::belongsTo('users', new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'student_id' => FilterableKey::string(),
            'name_family' => FilterableKey::string(),
            'name_family_yomi' => FilterableKey::string(),
            'name_given' => FilterableKey::string(),
            'name_given_yomi' => FilterableKey::string(),
            'email' => FilterableKey::string(),
            'tel' => FilterableKey::string(),
            'is_staff' => FilterableKey::bool(),
            'is_admin' => FilterableKey::bool(),
            'email_verified_at' => FilterableKey::isNull(),
            'univemail_verified_at' => FilterableKey::isNull(),
            'notes' => FilterableKey::string(),
            'created_at' => FilterableKey::datetime(),
            'updated_at' => FilterableKey::datetime(),
        ]));

        return new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'name' => FilterableKey::string(),
            'name_yomi' => FilterableKey::string(),
            'group_name' => FilterableKey::string(),
            'group_name_yomi' => FilterableKey::string(),
            'tags' => FilterableKey::belongsToMany(
                'circle_tag',
                'circle_id',
                'tag_id',
                $tags_choices,
                'name'
            ),
            'submitted_at' => FilterableKey::datetime(),
            // 不受理、受理、確認中
            'status' => FilterableKey::enum(['rejected', 'approved', 'NULL']),
            'status_set_at' => FilterableKey::datetime(),
            'status_set_by' => $users_type,
            'notes' => FilterableKey::string(),
            'created_at' => FilterableKey::datetime(),
            'updated_at' => FilterableKey::datetime(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function sortableKeys(): array
    {
        return [
            'id',
            'name',
            'name_yomi',
            'group_name',
            'group_name_yomi',
            'submitted_at',
            'status',
            'status_set_at',
            'status_set_by',
            'notes',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * @inheritDoc
     */
    public function map($record): array
    {
        $item = [];

        // カスタムフォームへの回答
        if (isset($this->custom_form)) {
            $answer = $record->answers->firstWhere('circle_id', $record->id);
            if (isset($answer) && isset($answer->details) && is_iterable($answer->details)) {
                foreach ($record->answers->where('circle_id', $record->id)->first()->details as $detail) {
                    if ($detail->question->type === 'upload') {
                        $item[self::CUSTOM_FORM_QUESTIONS_KEY_PREFIX . $detail->question_id] = [
                            'file_url' => route('staff.forms.answers.uploads.show', [
                                'form' => $this->custom_form->id,
                                'answer' => $answer->id,
                                'question' => $detail->question_id
                            ])
                        ];
                    } else {
                        $item[self::CUSTOM_FORM_QUESTIONS_KEY_PREFIX . $detail->question_id] = $detail;
                    }
                }
            }
        }

        // カスタムフォームへの回答以外の項目
        $keys_except_custom_forms = array_filter($this->keys(), function ($key) {
            return strpos($key, self::CUSTOM_FORM_QUESTIONS_KEY_PREFIX) !== 0;
        });

        foreach ($keys_except_custom_forms as $key) {
            switch ($key) {
                case 'status_set_by':
                    $item[$key] = $record->statusSetBy;
                    break;
                case 'status_set_at':
                    $item[$key] = !empty($record->status_set_at) ? $record->status_set_at->format('Y/m/d H:i:s') : null;
                    break;
                case 'created_at':
                    $item[$key] = $record->created_at->format('Y/m/d H:i:s');
                    break;
                case 'updated_at':
                    $item[$key] = $record->updated_at->format('Y/m/d H:i:s');
                    break;
                default:
                    $item[$key] = $record->$key;
            }
        }

        return $item;
    }

    protected function model(): Model
    {
        return new Circle();
    }
}
