from bs4 import BeautifulSoup
import requests

HEADERS = {'User-Agent': 'Mozilla/5.0 (iPad; CPU OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148'}
url="https://marvelofficial.com/shop/?fbclid=IwZXh0bgNhZW0CMTAAAR3OzSpffy0uw1Yeq72BBzE1fOb_DCkAK2pFNSAJI2WhkjbZZELRfwS_gpA_aem_i6ncYj8tTY9jABG0vPnrxw"


page=requests.get(url,headers=HEADERS)
soup=BeautifulSoup(page.text,'html.parser')
#print(soup)
#print(soup.find('div',class_='header-bottom wide-nav hide-for-sticky hide-for-medium'))
category_items = soup.find_all('div', class_='header-bottom wide-nav hide-for-sticky hide-for-medium')
#print(category_items)
category_items=soup.find_all('li')
print(category_items)
for item in category_items:
    a_tag = item.find('a')
    if a_tag:
        category_name = a_tag.get_text(strip=True)
        print(category_name)