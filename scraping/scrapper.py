from bs4 import BeautifulSoup
import requests

HEADERS = {'User-Agent': 'Mozilla/5.0 (iPad; CPU OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148'}
URL="https://marvelofficial.com/shop/?fbclid=IwZXh0bgNhZW0CMTAAAR3OzSpffy0uw1Yeq72BBzE1fOb_DCkAK2pFNSAJI2WhkjbZZELRfwS_gpA_aem_i6ncYj8tTY9jABG0vPnrxw"
urls=[]

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
    page=requests.get(url,headers=HEADERS)
    soup=BeautifulSoup(page.text,'html.parser')
    category_section = soup.find('div', class_='product-small box')
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

adressesOfSubclasses(urls)
printUrls(urls)

#<div class="product-small box ">
# 		<div class="box-image">
# 			<div class="image-fade_in_back">
# 				<a href="https://marvelofficial.com/product/mark-5-iron-man-helmet-1-1-replica/" aria-label="Mark 5 Iron Man Helmet 1:1 Replica">
# 					<img width="247" height="296" src="https://marvelofficial.com/wp-content/uploads/2020/09/B83A6311-14AE-4E5B-A1A4-48D7C101E4D3-247x296.jpeg" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="Iron Man Helmet MK 5 - Iron Man Prop Replica - Marvelofficial.com" decoding="async" fetchpriority="high"><img width="247" height="296" src="https://marvelofficial.com/wp-content/uploads/2020/09/6CDF96A3-11D4-472D-A792-EAE3E9EBAAA7-247x296.jpeg" class="show-on-hover absolute fill hide-for-small back-image" alt="" decoding="async">				</a>
# 			</div>
# 			<div class="image-tools is-small top right show-on-hover">
# 							</div>
# 			<div class="image-tools is-small hide-for-small bottom left show-on-hover">
# 							</div>
# 			<div class="image-tools grid-tools text-center hide-for-small bottom hover-slide-in show-on-hover">
# 							</div>
# 					</div>

# 		<div class="box-text box-text-products text-center grid-style-2" style="height: 141px;">
# 			<div class="title-wrapper"><p class="name product-title woocommerce-loop-product__title" style="height: 59.5px;"><a href="https://marvelofficial.com/product/mark-5-iron-man-helmet-1-1-replica/" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">Mark 5 Iron Man Helmet 1:1 Replica</a></p></div><div class="price-wrapper" style="height: 48.4px;"><div class="star-rating star-rating--inline" role="img" aria-label="Rated 4.85 out of 5"><span style="width:97%">Rated <strong class="rating">4.85</strong> out of 5</span></div>
# 	<span class="price"><del aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>590.75</bdi></span></del> <span class="screen-reader-text">Original price was: $590.75.</span><ins aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>410.75</bdi></span></ins><span class="screen-reader-text">Current price is: $410.75.</span></span>
# </div>		</div>
# 	</div>