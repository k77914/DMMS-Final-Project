# DataBase 期末專題 小組討論
成員列表：
| 112550019 | 112550047 | 112550059 | 112550099 | 112550131 |
| -------- | -------- | -------- | -------- | -------- | 
| 謝嘉宸     | 徐瑋晨     | 林佑丞     | 蔡烝旭     | 張詠晶     |
## Descriptions of our data
### Introduction to the data
資料集包含七個檔案(6個.csv file、 1個.geojson file)，每個檔案儲存有關於地點位於New York City的AirBnB之相關資料，以下是資料檔案內容說明：
1. calendar.csv(740.43 MB)
    >此資料檔案主要儲存各AirBnB的訂房與價位狀態，內含有日期資料、是否接受訂房、價錢資訊及最短與最長過夜天數限制等資料。
2. listings.csv(6.64 MB)
    >此資料檔案主要儲存AirBnB主人資訊及房間類型與評論相關的資料，內部含有主人姓名、AirBnB地點、房型及評論次數等資料。
3. listings_detailed.csv(103.49 MB)
    >此資料檔案主要儲存顧客對房間留下的各式評論。
7. neighbourhoods.csv(4.96 kB)
    >此資料檔案主要儲存紐約各個街區對應至紐約的哪個行政區。
9. reviews.csv(22.68 MB)
    >此資料檔案主要儲存評論產生的日期。
11. reviews_detailed.csv(344.63 MB)
    >此資料檔案主要儲存顧客對房間留下的各式評論。
13. neighbourhoods.geojson(634.17 kB)
    >此資料檔案主要儲存AirBNB的經緯座標
### Where is the data from
資料集來自於kaggle上的[Inside AirBnB-USA](https://www.kaggle.com/datasets/konradb/inside-airbnb-usa)

```
UPDATE hotel_reviews 
SET comments = REPLACE(REPLACE(comments, '</br>', ''), '<br/>', '');
```
    



