<?php
/* image_stats.php *************************************************
 * 统计 images / Categories / PicCategories 并输出带排序功能表格
 * 列标题已增加中文释义，避免仅用 a/b/c/d 等缩写
 ******************************************************************/
require_once __DIR__ . '/08_db_config.php';

/* ---------- 工具 ---------- */
function pct($num,$den){ return $den>0?number_format($num/$den*100,1).'%' :'-'; }
function nz($v){ return $v?:0; }

/* ---------- 全局统计 ---------- */
$y=(int)$mysqli->query("SELECT COUNT(*) FROM images")->fetch_row()[0];
$x=(int)$mysqli->query("SELECT COUNT(*) FROM PicCategories")->fetch_row()[0];
$w=(int)$mysqli->query("SELECT COUNT(DISTINCT image_id) FROM PicCategories")->fetch_row()[0];
$z=$y-$w;

$unclassified=$mysqli->query("
  SELECT
    SUM(image_exists=1)                    AS g,
    SUM(likes-dislikes>=30)                AS h,
    SUM(likes-dislikes BETWEEN 20 AND 29)  AS i,
    SUM(likes-dislikes BETWEEN 10 AND 19)  AS j,
    SUM(likes-dislikes BETWEEN  0 AND  9)  AS k
  FROM images
  WHERE id NOT IN (SELECT DISTINCT image_id FROM PicCategories)
")->fetch_assoc();
$g=nz($unclassified['g']); $h=nz($unclassified['h']); $i=nz($unclassified['i']);
$j=nz($unclassified['j']); $k=nz($unclassified['k']);

/* ---------- 分类统计 ---------- */
$sql="
SELECT c.id,c.category_name,c.kindID,
  COUNT(i.id)                                   AS a,
  SUM(i.image_exists=1)                         AS b,
  SUM(i.image_exists=0)                         AS c0,
  SUM(ldiff>=30)                                AS e,
  SUM(ldiff>=30  AND i.image_exists=1)          AS f,
  SUM(ldiff BETWEEN 20 AND 29)                  AS g20,
  SUM(ldiff BETWEEN 20 AND 29 AND i.image_exists=1) AS g20e,
  SUM(ldiff BETWEEN 10 AND 19)                  AS g10,
  SUM(ldiff BETWEEN 10 AND 19 AND i.image_exists=1) AS g10e,
  SUM(ldiff BETWEEN  0 AND  9)                  AS g0,
  SUM(ldiff BETWEEN  0 AND  9 AND i.image_exists=1) AS g0e,
  SUM(ldiff BETWEEN  5 AND  9)                  AS g5,
  SUM(ldiff BETWEEN  5 AND  9 AND i.image_exists=1) AS g5e,
  SUM(ldiff BETWEEN  2 AND  4)                  AS g2,
  SUM(ldiff BETWEEN  2 AND  4 AND i.image_exists=1) AS g2e,
  SUM(ldiff=0)                                  AS g_eq0,
  SUM(ldiff=0  AND i.image_exists=1)            AS g_eq0e,
  SUM(ldiff=1)                                  AS g_eq1,
  SUM(ldiff=1  AND i.image_exists=1)            AS g_eq1e,
  SUM(ldiff=2)                                  AS g_eq2,
  SUM(ldiff=2  AND i.image_exists=1)            AS g_eq2e,
  SUM(ldiff=3)                                  AS g_eq3,
  SUM(ldiff=3  AND i.image_exists=1)            AS g_eq3e,
  SUM(ldiff=4)                                  AS g_eq4,
  SUM(ldiff=4  AND i.image_exists=1)            AS g_eq4e,
  SUM(ldiff=5)                                  AS g_eq5,
  SUM(ldiff=5  AND i.image_exists=1)            AS g_eq5e
FROM Categories c
LEFT JOIN PicCategories pc ON c.id = pc.category_id
LEFT JOIN (SELECT *,likes-dislikes AS ldiff FROM images) i
       ON i.id = pc.image_id
GROUP BY c.id";
$res=$mysqli->query($sql);
$rows=[];
while($r=$res->fetch_assoc()){
  $r['d_pct']=$r['a']?round($r['b']/$r['a']*100,1):0;
  $rows[]=$r;
}
usort($rows,fn($x,$y)=>$y['d_pct']<=>$x['d_pct']);
?>
<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<title>分类统计</title>
<style>
body{font-family:sans-serif;}
table{border-collapse:collapse;font-size:12px;white-space:nowrap;}
th,td{border:1px solid #ccc;padding:4px 6px;text-align:right;}
th:nth-child(-n+2),td:nth-child(-n+2){text-align:left;}
thead th{position:sticky;top:0;background:#f8f8f8;}
tbody tr:nth-child(even){background:#f9f9ff;}
select.sort{font-size:10px;width:48px;margin-top:2px;}
</style>
</head>
<body>
<h2>分类统计</h2>

<div style="overflow-x:auto">
<table id="statTable">
<thead>
<tr>
  <th>分类名</th><th>kindID</th>
<?php
/* ---------- 构造带中文释义的列标题 ---------- */
$baseHeaders=[
  '分类内图片数<br>(a)','a ÷ x %','存在图片<br>(b)','缺失图片<br>(c)','存在率<br>(d=b/a%)'
];
$groupDefs=[
  ['≥30','e','f'],
  ['20‑29','g20','g20e'],
  ['10‑19','g10','g10e'],
  ['0‑9','g0','g0e'],
  ['5‑9','g5','g5e'],
  ['2‑4','g2','g2e'],
  ['差值=0','g_eq0','g_eq0e'],
  ['差值=1','g_eq1','g_eq1e'],
  ['差值=2','g_eq2','g_eq2e'],
  ['差值=3','g_eq3','g_eq3e'],
  ['差值=4','g_eq4','g_eq4e'],
  ['差值=5','g_eq5','g_eq5e']
];
$headerLabels=$baseHeaders;
foreach($groupDefs as $gd){
    $headerLabels[]=$gd[0].' 数<br>('.$gd[1].')';
    $headerLabels[]=$gd[0].' ÷ a %';
    $headerLabels[]=$gd[0].' 存在率';
}
foreach($headerLabels as $lbl){
    echo "<th>{$lbl}<br><select class='sort'></select></th>";
}
?>
</tr>
</thead>
<tbody>
<?php foreach($rows as $r): ?>
<?php
   $a=$r['a']?:0.0001;
   $groups=[['e','f'],['g20','g20e'],['g10','g10e'],['g0','g0e'],
            ['g5','g5e'],['g2','g2e'],['g_eq0','g_eq0e'],['g_eq1','g_eq1e'],
            ['g_eq2','g_eq2e'],['g_eq3','g_eq3e'],['g_eq4','g_eq4e'],['g_eq5','g_eq5e']];
?>
<tr>
  <td><?=htmlspecialchars($r['category_name'])?></td>
  <td><?=htmlspecialchars($r['kindID'])?></td>
  <td><?=$r['a']?></td><td><?=pct($r['a'],$x)?></td>
  <td><?=$r['b']?></td><td><?=$r['c0']?></td><td><?=pct($r['b'],$r['a'])?></td>

  <?php foreach($groups as $grp):
        $cnt=nz($r[$grp[0]]); $cnt1=nz($r[$grp[1]]); ?>
    <td><?=$cnt?></td><td><?=pct($cnt,$r['a'])?></td><td><?=pct($cnt1,$cnt)?></td>
  <?php endforeach; ?>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<hr>
<h3>全局统计</h3>
<p>x (分类记录总数)= <?=$x?>；y (图片总数)= <?=$y?>；z (无分类图片)= <?=$z?>；w (有分类图片)= <?=$w?>。</p>
<p>数据库中<strong>不属于任何分类</strong>的图片占比：<?=pct($z,$y)?></p>
<p>其中 <code>image_exists = 1</code> 的图片数量 g = <?=$g?>，占比 <?=pct($g,$z)?></p>
<p>[30,+∞)：h = <?=$h?>，占比 <?=pct($h,$z)?></p>
<p>[20,30)：i = <?=$i?>，占比 <?=pct($i,$z)?></p>
<p>[10,20)：j = <?=$j?>，占比 <?=pct($j,$z)?></p>
<p>[0,10)：k = <?=$k?>，占比 <?=pct($k,$z)?></p>

<script>
/* ---------- 给每个数字列绑定排序下拉 ---------- */
const ths=document.querySelectorAll('#statTable thead th');
ths.forEach((th,idx)=>{
   const sel=th.querySelector('select.sort');
   if(!sel) return;
   sel.dataset.col=idx;
   sel.innerHTML='<option value="">↕</option><option value="asc">↑</option><option value="desc">↓</option>';
   sel.addEventListener('change',function(){
       const order=this.value;if(!order)return;
       const col=parseInt(this.dataset.col);
       const tbody=document.querySelector('#statTable tbody');
       const rows=[...tbody.querySelectorAll('tr')];
       rows.sort((r1,r2)=>{
          const v1=parseFloat(r1.children[col].innerText.replace('%',''))||0;
          const v2=parseFloat(r2.children[col].innerText.replace('%',''))||0;
          return order==='asc'?v1-v2:v2-v1;
       });
       rows.forEach(r=>tbody.appendChild(r));
       document.querySelectorAll('select.sort').forEach(s=>{if(s!==this)s.value='';});
   });
});
</script>
</body>
</html>
