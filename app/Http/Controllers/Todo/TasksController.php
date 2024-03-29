<?php

namespace App\Http\Controllers\Todo;

use Validator;
use App\Task;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tasks = Task::orderBy('created_at', 'asc')->get();
        $filter = $request->input('filter');
        $filteredTasks = $tasks;
        if ($filter == 'active') {
            $filteredTasks = $tasks->filter(function ($value, $key) {
                return $value->completed == '0';
            });
        } else if ($filter == 'completed') {
            $filteredTasks = $tasks->filter(function ($value, $key) {
                return $value->completed == '1';
            });
        }
        //ソート済みのタスクをデータベースから取得
        //orderBy 指定したカラムでクエリ結果をソート
        //asc　昇順 desc 降順
        //created_at 作成時間　update_at 更新時間
        return view('tasks', ['tasks' => $tasks, 'filteredTasks' => $filteredTasks, 'filter' => $filter]);
        /*viewの第二引数に配列を渡すと，配列のキー名を変数名としてビュー中で
            使えるようになる。
            */
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //タスクの追加
        $filter = $request->input('filter');
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            //フィールドが入力データに存在しており、かつ空でないことをバリデートする。
        ]);
        /*
    Validator::makeメソッドの第１引数は、バリデーションを行うデータ
    第２引数はそのデータに適用するバリデーションルール
    Validatorファサードのmake関数は新しいバリデータインスタンスを生成
    ファサード：アプリケーションのサービスコンテナに登録したクラスへ、静的なインターフェイスを提供する。
    */

        if ($validator->fails()) { //入力値が無効か確認

            return redirect('/')
                ->withInput() //全ての入力内容をセッションに保存
                ->withErrors($validator);
            /*    ->withErrors($validator)で、$validatorインスタンスのエラーをセッションに保存します。
            すると、全てのビューから$errors変数としてアクセスできるようになります。*/
        }
        $task = new Task; //Taskインスタンスの生成
        $task->name = $request->name; //$taskのnameに$requestのnameを
        $task->save(); //DBに保存

        return redirect('/' . '?filter=' . $filter); // /ルート(view(tasks))にリダイレクト
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $filter = $request->input('filter');
        $task = Task::find($id);
        if ($task->completed == '1') {
            $task->completed = '0';
        } else {
            $task->completed = '1';
        }
        $task->save(); //DBに保存
        return redirect('/' . '?filter=' . $filter);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateAll(Request $request)
    {
        //completedの処理
        //Task::where('id', $id)->first();
        $filter = $request->input('filter');

        $tasks = Task::orderBy('created_at', 'asc')->get();
        $i = 0;
        foreach ($tasks as $task) {
            if ($task->completed == '1') $i++;
        }
        if ($i == count($tasks)) {
            foreach ($tasks as $task) {
                $task->completed = '0';
                $task->save(); //DBに保存
            }
        } else {
            foreach ($tasks as $task) {
                $task->completed = '1';
                $task->save(); //DBに保存
            }
        }
        return redirect('/' . '?filter=' . $filter);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        //
        $filter = $request->input('filter');

        Task::findOrFail($id)->delete();
        //findOrFail 該当するレコードが見つからない場合例外を投げる
        //Taskでdelete()を呼ぶ　テーブルからレコードを削除するクエリビルダ 
        return redirect('/' . '?filter=' . $filter);
    }
}
