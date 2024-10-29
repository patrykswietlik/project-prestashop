from bs4 import BeautifulSoup
import requests

HEADERS = {'User-Agent': 'Mozilla/5.0 (iPad; CPU OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148'}
url="https://marvelofficial.com/shop/?fbclid=IwZXh0bgNhZW0CMTAAAR3OzSpffy0uw1Yeq72BBzE1fOb_DCkAK2pFNSAJI2WhkjbZZELRfwS_gpA_aem_i6ncYj8tTY9jABG0vPnrxw"


page=requests.get(url,headers=HEADERS)
soup=BeautifulSoup(page.text,'html.parser')
#print(soup)
#print(soup.find('div',class_='header-bottom wide-nav hide-for-sticky hide-for-medium'))
category_section = soup.find('div', id='wrapper')
category_section=category_section.find('div',class_='header-wrapper')
category_section = soup.find('div', id='wide-nav')
#category_section=category_section.find('div',class_='flex-col.hide-for-medium.flex-left')
print(category_section)
if category_section:
    header=category_section.find_all('li')
    #print(header)
    for item in header:
        a_tag = item.find('a',class_='nav-top-link')
        if a_tag:
            category_name = a_tag.get_text(strip=True)
            print(category_name)
else:
    print("empty")