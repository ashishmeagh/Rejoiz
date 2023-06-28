<?php

namespace App\Exceptions;

use Exception;
use Redirect;
use Flash;
use Log;
use Session;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Exceptions\PostTooLargeException;


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        // dd($exception,$exception->getMessage(),strpos($exception->getMessage(), "Undefined index") !== -1);
        if($exception instanceof NotFoundHttpException)
        {
            return response()->view('errors.404', [],404);
        }
        if($exception instanceof  \Illuminate\Http\Exceptions\PostTooLargeException)
        {
           return Redirect::back()->withErrors('too large');
        }

        /* Temporary Fix for Handling Undefined Index in All Pages : 25-11-2020 */

        if(strpos($exception->getMessage(), "Undefined index") !== -1)
        {
            if($request->wantsJson())
            {
                return parent::render($request, $exception);
            }
            else
            {
                return response()->view('errors.500', ['message'=>$exception->getMessage()],404);
            }
        }
        

        if(env('APP_DEBUG') == false)
        {
            $exception = '
                ---------- Critical Exception --------------\n
                Description: '.$exception->getMessage().',\n
                Line No: '.$exception->getLine().',\n
                Filename: '.$exception->getFile().',\n
            ';

        
            $this->report_on_slack($exception);    
        }
        
        return parent::render($request, $exception);
    }
    public function report_on_slack($messsage = 'None'){

        try
        {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://hooks.slack.com/services/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');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"text\":\"$messsage\"}");


            $headers = array();
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


            $result = curl_exec($ch);
            
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch); 
        }
        catch(\Exception $e)
        {
            dd($e);
        }
        
    }
}
