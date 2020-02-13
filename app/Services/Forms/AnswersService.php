<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Eloquents\Form;
use App\Eloquents\Circle;
use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\User;
use App\Services\Forms\AnswerDetailsService;
use App\Http\Requests\Forms\BaseAnswerRequest;
use App\Mail\Forms\AnswerConfirmationMailable;
use Illuminate\Database\Eloquent\Collection;
use DB;
use Illuminate\Support\Facades\Mail;

class AnswersService
{
    private $answerDetailsService;

    public function __construct(AnswerDetailsService $answerDetailsService)
    {
        $this->answerDetailsService = $answerDetailsService;
    }

    /**
     * 団体所属者にメールを送信する
     *
     * @return void
     */
    public function sendAll(Answer $answer)
    {
        // TODO: 希望するスタッフも確認メールを受信できるようにする
        // 団体にメールを送る
        $answer->loadMissing('form.questions');
        $answer->loadMissing('circle.users');
        $answer_details = $this->answerDetailsService->getAnswerDetailsByAnswer($answer);

        foreach ($answer->circle->users as $user) {
            $this->sendToUser(
                $answer->form,
                $answer->form->questions,
                $answer->circle,
                $user,
                $answer,
                $answer_details
            );
        }
    }

    private function sendToUser(
        Form $form,
        Collection $questions,
        Circle $circle,
        User $user,
        Answer $answer,
        array $answer_details
    ) {
        Mail::to($user)
            ->send(
                (new AnswerConfirmationMailable($form, $questions, $circle, $user, $answer, $answer_details))
                    ->replyTo(config('portal.contact_email'), config('portal.admin_name'))
                    ->subject('申請「' . $form->name . '」を承りました')
            );
    }

    public function getAnswersByCircle(Form $form, Circle $circle)
    {
        return Answer::where('form_id', $form->id)->where('circle_id', $circle->id)->get();
    }

    public function createAnswer(Form $form, Circle $circle, BaseAnswerRequest $request)
    {
        return DB::transaction(function () use ($form, $circle, $request) {
            $answer_details = $this->answerDetailsService->getAnswerDetailsWithFilePathFromRequest($form, $request);

            $answer = Answer::create([
                'form_id' => $form->id,
                'circle_id' => $circle->id,
            ]);

            $this->answerDetailsService->updateAnswerDetails($form, $answer, $answer_details);

            return $answer;
        });
    }

    public function updateAnswer(Form $form, Answer $answer, BaseAnswerRequest $request)
    {
        return DB::transaction(function () use ($form, $answer, $request) {
            $answer_details = $this->answerDetailsService->getAnswerDetailsWithFilePathFromRequest($form, $request);

            $answer->update();
            $this->answerDetailsService->updateAnswerDetails($form, $answer, $answer_details);

            return $answer;
        });
    }
}
