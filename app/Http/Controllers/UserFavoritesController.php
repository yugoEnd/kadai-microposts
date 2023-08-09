<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class UserFavoritesController extends Controller
{
    /**
     * 投稿をお気に入りにするアクション。
    *
    * @param  $id  投稿のid
     * @return \Illuminate\Http\Response
     */
    public function store($id)
    {
     
         \Auth::user()->favorite($id);
        
         // 前のURLへリダイレクトさせる
          return back();
        
    }

    /**
    * お気に入り投稿を解除するアクション。
    *
    * @param  $id  投稿のid
    * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        // 認証済みユーザ（閲覧者）が、 idの投稿をお気に入り解除する
  
        \Auth::user()->unfavorite($id);
        // 前のURLへリダイレクトさせる
        return back();
    }
}

