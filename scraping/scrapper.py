from bs4 import BeautifulSoup
from selenium import webdriver
import requests
import re
import os
import json

HEADERS = {'User-Agent': 'Mozilla/5.0 (iPad; CPU OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148'}
URL="https://marvelofficial.com/shop/?fbclid=IwZXh0bgNhZW0CMTAAAR3OzSpffy0uw1Yeq72BBzE1fOb_DCkAK2pFNSAJI2WhkjbZZELRfwS_gpA_aem_i6ncYj8tTY9jABG0vPnrxw"
urls=[]
product_details=[]
product_visited=[]

def adressesOfSubclasses(urls):
    page=requests.get(URL,headers=HEADERS)
    soup=BeautifulSoup(page.text,'html.parser')
    category_section = soup.find('div', id='wide-nav')
    if category_section:
        links=category_section.find_all('li')
        for item in links:
            a_tag = item.find('a',class_='nav-top-link')
            if a_tag:
                category_list = [a_tag['href']] 
                sub_menu = item.find('ul', class_='sub-menu nav-dropdown nav-dropdown-simple')
                if sub_menu:
                    for sub_item in sub_menu.find_all('a'):
                        category_list.append(sub_item['href'])
                urls.append(category_list)
    else:
        print("empty")

def printUrls(urls):
    for category_list in urls:
        print("Główna kategoria:", category_list[0])
        if len(category_list) > 1:
            print("  Podkategorie:")
            for sub_link in category_list[1:]:
                print("    -", sub_link)
        print() 

def gettingNamesofSubcattegories():
    page=requests.get(URL,headers=HEADERS)
    soup=BeautifulSoup(page.text,'html.parser')
    category_section = soup.find('div', id='wide-nav')
    print(category_section)
    if category_section:
        header=category_section.find_all('li')
        for item in header:
            a_tag = item.find('a',class_='nav-top-link')
            if a_tag:
                category_name = a_tag.get_text(strip=True)
                print("["+category_name+"]")
            else:
                a2_tag = item.find('a')
                if a2_tag:
                    category_name = a2_tag.get_text(strip=True)
                    print(category_name)
    else:
        print("empty")

def productsCategories(url):
    products=[]
    #products.append(url)
    page=requests.get(url,headers=HEADERS)
    soup=BeautifulSoup(page.text,'html.parser')
    while True:
        category_section = soup.findAll('div', class_='product-small box')
        for item in category_section:
            infos=item.find('div',class_='box-text box-text-products text-center grid-style-2')
            if infos:
                text=infos.find('a',class_='woocommerce-LoopProduct-link woocommerce-loop-product__link')
                product_info = [text['href']] 
    
                if product_info[0] not in product_visited: #sprawdzamy czy dany obiekt juz scrappowany
                    product_visited.append(product_info[0])
                    product_details.append(productSite(product_info[0]))

                sub_item=text.get_text(strip=True)
                product_info.append(sub_item)
                rating=infos.find('div', class_='star-rating star-rating--inline')
                if rating:
                    sub_item=rating.get_text(strip=True)
                    product_info.append(sub_item)
                else:
                    sub_item=None
                    product_info.append(sub_item)
                prices=infos.findAll('span',class_='woocommerce-Price-amount amount')
                for price in prices:
                    product_info.append(price.get_text(strip=True))
                if len(prices)==1:
                    product_info.append(None)
                products.append(product_info)
        next_page=soup.find('a', class_='next page-number')
        if next_page:
            href = next_page['href']
            page=requests.get(href,headers=HEADERS)
            soup=BeautifulSoup(page.text,'html.parser')
        else:
            print("Link nie znaleziony.")
            break
    #print(products)

def saveImages(images,infos):
    folder = "Mark5"
    #infos[0]
    print(images)
    if not os.path.exists(folder):
        os.makedirs(folder)
    else:
        print(f"Folder '{folder}' already exists. Proceeding with downloads.")
    print(f"jestem w f-cji nazwa folderu to {folder}")
    for idx, url in enumerate(images):
        try:
            response = requests.get(url, stream=True)
            if response.status_code == 200:
                file_name = os.path.join(folder, f'image_{idx + 1}.jpg')
                with open(file_name, 'wb') as file:
                    file.write(response.content)
                print(f"Downloaded: {file_name}")
            else:
                print(f"Failed to download {url} - Status code: {response.status_code}")
        except Exception as e:
            print(f"Error downloading {url} - {e}")

def getFullSizeUrl(thumbnail_url):
    # Use a regex to remove the size part (e.g., -247x296)
    full_size_url = re.sub(r'-\d+x\d+(?=\.\w+$)', '', thumbnail_url)
    return full_size_url

def productSite(url):
    product=[]
    product.append(url)
    image_urls=[]
    page=requests.get(url,headers=HEADERS)
    soup=BeautifulSoup(page.text,'html.parser')
    # driver = webdriver.Chrome()  # Ensure you have the ChromeDriver installed
    # driver.get(url)
    # driver.implicitly_wait(5)
    # soup_images = BeautifulSoup(driver.page_source, 'html.parser')
    # images = soup_images.find('div', class_='flickity-slider')
    # if images:
    #     for img_tag in images.find_all('img'):
    #         src = img_tag.get('src')
    #         if src:
    #             image_urls.append(getFullSizeUrl(src))
    # else:
    #     print(f"kurwa nie ma tych images")
    # driver.quit()

    #krotkie info
    infos=soup.find('div',class_='product-short-description')
    if infos:
        delivery=infos.find('span')
        if delivery:
            full_name_tag=infos.find_all('p')[1]
            if full_name_tag:
                title = full_name_tag.get_text(strip=True)
                product.append(title)
            delivery=delivery.get_text(strip=True)
            product.append(delivery)
        else:
            full_name_tag=infos.find_all('p')[0]
            if full_name_tag:
                title = full_name_tag.get_text(strip=True)
                product.append(title)
            product.append(None)
        properties=infos.find_all('li')
        properties_list=[]
        for property_m in properties:
            property=property_m.get_text(strip=True)
            properties_list.append(property)
        product.append(properties_list)
        stock=soup.find('p',class_="stock in-stock")
        if stock:
            stock=stock.get_text(strip=True)
            product.append(stock)
        else:
            product.append(None)
    print(product)
    #opis produktu
    description_div=soup.find('div',class_="woocommerce-Tabs-panel woocommerce-Tabs-panel--description panel entry-content active")
    content = []
    if description_div:
        for element in description_div.find_all(recursive=False):
            if element.name in ['p', 'h2', 'ul', 'li']:
                if element.name in ['p', 'h2']:
                    content.append(element.get_text(strip=True))
                elif element.name == 'ul':
                    for li in element.find_all('li'):
                        content.append(f"- {li.get_text(strip=True)}")
    else:
        print("Description div not found.")
    
    for line in content:
        print(line)
    product.append(content)
  #  saveImages(image_urls,product)
    return product
  

gettingNamesofSubcattegories()
adressesOfSubclasses(urls)

# printUrls(urls)
for url in urls:
        productsCategories(url[0])
        if len(url) > 1:
            for sub_link in url[1:]:
                productsCategories(sub_link)
#printUrls(urls)
#productSite('https://marvelofficial.com/product/black-panther-airpods-pro-silicon-case-marvel/')