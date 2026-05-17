<?php

namespace App\Traits;

trait ApiResponser
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    protected function responseSuccess($data = null, $message = null, $code = 200)
	{
		return response()->json([
			'status'            => 'success',
            'status_code'       => $code,
			'message'           => $message,
			'data'              => $data
		], $code);
	}

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
	protected function responseError($message = null, $code = 400, $data = null)
	{
		return response()->json([
			'status'            => 'error',
			'status_code'       => $code,
			'message'           => $message,
			'data'              => $data
		], $code);
	}
}
