<?php
/**
 * Created by PhpStorm.
 * User: hiliq
 * Date: 2019/2/9
 * Time: 21:19
 */
namespace app\api\controller;
use app\model\Author;
use app\model\Book;
use app\model\Photo;
use think\Controller;
use think\Request;
use app\model\Chapter;
use think\facade\App;
class Index extends Controller
{
    protected $chapterService;
    protected $photoService;
    public function initialize()
    {
        $this->chapterService = new \app\service\ChapterService();
        $this->photoService = new \app\service\PhotoService();
    }
    public function save(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->param();
            $book = Book::where('book_name', '=', trim($data['book_name']))->find();
            if (!$book) {
                $author = Author::where('author_name', '=', trim($data['author']))->find();
                if (is_null($author)) {//如果作者不存在
                    $author = new Author();
                    $author->author_name = $data['author'];
                    $author->save();
                }
                $book = new Book();
                $book->author_id = $author->id;
                $book->book_name = trim($data['book_name']);
                $book->nick_name = trim($data['nick_name']);
                $book->tags = trim($data['tags']);
                $book->src = trim($data['src']);
                $book->end = trim($data['end']);
                $book->cover_url = trim($data['cover_url']);
                $book->save();
            }
            $map[] = ['chapter_name', '=', trim($data['chapter_name'])];
            $map[] = ['book_id', '=', $book->id];
            $chapter = Chapter::where($map)->find();
            if (!$chapter) {
                $chapter = new Chapter();
                $chapter->chapter_name = trim($data['chapter_name']);
                $chapter->book_id = $book->id;
                $lastChapterOrder = 0;
                $lastChapter = $this->chapterService->getLastChapter($book->id);
                if ($lastChapter){
                    $lastChapterOrder = $lastChapter->order;
                }
                $chapter->order = $lastChapterOrder + 1;
                $chapter->save();
            }
            preg_match_all('/src=\"(.+?)\"/is', $data['images'], $img_urls);
            foreach ($img_urls[1] as $img_url){
                $photo = new Photo();
                $photo->chapter_id  = $chapter->id;
                $lastOrder = 0;
                $lastPhoto = $this->photoService->getLastPhoto($chapter->id);
                if ($lastPhoto){
                    $lastOrder = $lastPhoto->order;
                }
                $photo->order = $lastOrder + 1;
                $photo->img_url = $img_url;
                $photo->save();
            }
        }
    }
}
